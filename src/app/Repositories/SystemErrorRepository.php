<?php

namespace App\Repositories;

use App\Models\{ 
    SystemError
};
use Carbon\Carbon;
use Illuminate\Support\Facades\Session;

class SystemErrorRepository
{
    public function saveException(\Throwable $exception, string $category = 'backend')
    {
        // make sure that it is possible to establish a database connection
        $request = request();
        $user = $request->user();
        $message = get_class($exception).(! empty($exception->getMessage()) ? ': '.$exception->getMessage() : '');

        if (strlen($message) > 1024) {
            $message = substr($message, 0, 1024);
        }

        $error = $exception->getFile().':'.$exception->getLine()."\n". //
            $exception->getTraceAsString()."\n\n". //
            print_r($_COOKIE, true)."\n";

        SystemError::create([
            'message'    => $message,
            'url'        => $request->fullUrl(),
            'ip'         => array_key_exists('REMOTE_ADDR', $_SERVER) ? $_SERVER['REMOTE_ADDR'] : null,
            'category'   => $category,
            'error'      => $error,
            'account_id' => $user !== null ? $user->id : null,
            'session_id' => Session::getId()
        ]);
    }

    public function saveFrontendException(string $url, string $message, string $error, string $category)
    {
        $userAgent = $_SERVER['HTTP_USER_AGENT'];
        if (strlen($userAgent) > 190) {
            $userAgent = substr($userAgent, 0, 190).'...';
        }

        $request = request();
        $user = $request->user();

        if ($error !== null) {
            $error .= "\n\n".print_r($_COOKIE, true)."\n";
        }

        SystemError::create([
            'message'    => $message,
            'url'        => $url,
            'ip'         => isset($_SERVER['REMOTE_ADDR'])
                ? $_SERVER['REMOTE_ADDR']
                : null,
            'error'      => $error,
            'account_id' => $user !== null
                ? $user->id 
                : null,
            'category'   => $category,
            'session_id' => Session::getId(),
            'user_agent' => $userAgent,
        ]);

    }

    public function deleteOlderThan(Carbon $date) 
    {
        SystemError::where('created_at', '<', $date)->delete();
    }
}
