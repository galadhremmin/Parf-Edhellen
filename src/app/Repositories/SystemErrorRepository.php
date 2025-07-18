<?php

namespace App\Repositories;

use App\Models\SystemError;
use Carbon\Carbon;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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
        ]);
    }

    public function saveFrontendException(string $url, string $message, string $error, string $category)
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
        ]);

    }

    public function deleteOlderThan(Carbon $date)
    {
        SystemError::where('created_at', '<', $date)->delete();
    }
}
