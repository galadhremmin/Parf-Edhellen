<?php

namespace App\Repositories;

use App\Models\SystemError;
use Carbon\Carbon;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Session;
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
            'message' => 'Expensive request detected',
            'category' => 'performance',
            'error' => json_encode([
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
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
            'file' => null,
            'line' => null,
            'user_agent' => $request->userAgent(),
            'account_id' => $request->user()?->id,
            'session_id' => Session::getId(),
            'url' => $request->fullUrl(),
            'ip' => $request->ip(),
            'duration' => self::calculateRequestDuration(),
        ];

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
        if (defined('LARAVEL_START')) {
            return microtime(true) - LARAVEL_START;
        }
        
        if (isset($_SERVER['REQUEST_TIME_FLOAT'])) {
            return microtime(true) - (float) $_SERVER['REQUEST_TIME_FLOAT'];
        }

        return null;
    }
}
