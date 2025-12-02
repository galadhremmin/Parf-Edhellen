<?php

namespace App\Repositories;

use App\Models\SystemError;
use Carbon\Carbon;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Session;
use App\Exceptions\SuspiciousBotActivityException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Http\{
    Request,
    Response,
    JsonResponse,
};

class SystemErrorRepository
{
    public function saveException(\Throwable $exception, string $category = 'backend'): SystemError
    {
        if ($exception instanceof NotFoundHttpException) {
            $category = 'http-404';
        }

        if ($exception instanceof AuthenticationException) {
            $category = 'http-401';
        }

        // make sure that it is possible to establish a database connection
        $request = request();
        $user = $request->user();
        $message = get_class($exception).(! empty($exception->getMessage()) ? ': '.$exception->getMessage() : '');

        if (strlen($message) > 1024) {
            $message = substr($message, 0, 1024);
        }

        $error = $exception->getFile().':'.$exception->getLine()."\n". //
            $exception->getTraceAsString()."\n\n". //
            print_r($request->cookie(), true)."\n";

        if ($exception instanceof SuspiciousBotActivityException && $exception->getAssessmentResult() !== null) {
            $error .= "\n\n".'Recaptcha assessment result: '.print_r($exception->getAssessmentResult(), true);
        }

        $duration = self::calculateRequestDuration();
        
        return SystemError::create([
            'message' => $message,
            'category' => $category,
            'error' => $error,
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'account_id' => $user !== null ? $user->id : null,
            'session_id' => Session::getId(),
            'url' => $request->fullUrl(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'duration' => $duration ?? null,
        ]);
    }

    public function saveExpensiveRequest(Request $request, Response|JsonResponse $response)
    {
        $logData = [
            'message' => 'Expensive request detected (threshold: '.config('ed.expensive_request_threshold').'ms)',
            'category' => 'performance',
            'error' => [
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'route' => $request->route()?->getName() ?? $request->route()?->uri(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'status_code' => $response->getStatusCode(),
                'memory_usage_mb' => round(memory_get_peak_usage(true) / 1024 / 1024, 2),
                'user_id' => $request->user()?->id,
                'timestamp' => now()->toISOString(),
                'request_size_kb' => round(strlen($request->getContent()) / 1024, 2),
                'response_size_kb' => round(strlen($response->getContent()) / 1024, 2),
                'query_params' => $request->isMethod('GET') ? $request->query() : null,
                'post_body' => null,
                'controller' => null,
            ],
            'file' => null,
            'line' => null,
            'user_agent' => $request->userAgent(),
            'account_id' => $request->user()?->id,
            'session_id' => Session::getId(),
            'url' => $request->fullUrl(),
            'ip' => $request->ip(),
            'duration' => self::calculateRequestDuration(),
        ];

        // Get the controller serving the route, if available
        $route = $request->route();
        if ($route && method_exists($route, 'getAction')) {
            $action = $route->getAction();
            if (isset($action['controller'])) {
                $logData['error']['controller'] = $action['controller'];
            }
        }

        if (in_array($logData['error']['route'], config('ed.expensive_request_post_logging_routes'))) {
            if ($request->isMethod('POST') || 
                $request->isMethod('PUT')) {
                $logData['error']['post_body'] = $request->getContent();
            } else {
                $logData['error']['post_body'] = 'restricted, see ED_EXPENSIVE_REQUEST_POST_LOGGING_ROUTES.';
            }
        }

        // make sure to serialize the error array before persisting it
        $logData['error'] = print_r($logData['error'], true);

        return SystemError::create($logData);
    }

    public function saveFrontendException(string $url, string $message, string $error, string $category, ?float $duration = null)
    {
        $request = request();
        $user = $request->user();

        if ($error !== null) {
            $error .= "\n\n".print_r($request->cookie(), true)."\n";
        }

        SystemError::create([
            'message' => $message,
            'url' => $url,
            'error' => $error,
            'account_id' => $user !== null
                ? $user->id
                : null,
            'category' => $category,
            'session_id' => Session::getId(),
            'user_agent' => $request->userAgent(),
            'ip' => $request->ip(),
            'duration' => $duration,
        ]);

    }

    public function deleteOlderThan(Carbon $date): void
    {
        SystemError::where('created_at', '<', $date)->delete();
    }

    private static function calculateRequestDuration(): ?float
    {
        $duration = null;
        if (defined('LARAVEL_START')) {
            $duration = microtime(true) - LARAVEL_START;
        }
        
        if (isset($_SERVER['REQUEST_TIME_FLOAT'])) {
            $duration = microtime(true) - (float) $_SERVER['REQUEST_TIME_FLOAT'];
        }

        if ($duration !== null) {
            $duration = round($duration * 1000, 2); // milliseconds
        }

        return $duration;
    }
}
