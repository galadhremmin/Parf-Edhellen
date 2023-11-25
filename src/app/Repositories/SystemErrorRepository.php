<?php

namespace App\Repositories;

use App\Models\{ 
    SystemError
};
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
}
