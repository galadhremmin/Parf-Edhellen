<?php

namespace App\Exceptions;

use Exception;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use App\Models\SystemError;

class DBHandler extends Handler
{
    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Exception  $exception
     * @return void
     */
    public function report(Exception $exception)
    {
        $shouldIgnore = $exception instanceof AuthenticationException ||
                        $exception instanceof NotFoundHttpException ||
                        $exception instanceof ValidationException ||
                        $exception instanceof TokenMismatchException;

        // make sure that it is possible to establish a database connection
        if (! $shouldIgnore) {
            $request = request();
            $user = $request->user();
            $common = $this->shouldReport($exception);

            SystemError::create([
                'message'    => get_class($exception).(! empty($exception->getMessage()) ? ': '.$exception->getMessage() : ''),
                'url'        => $request->fullUrl(),
                'ip'         => array_key_exists('REMOTE_ADDR', $_SERVER) ? $_SERVER['REMOTE_ADDR'] : null,
                'is_common'  => $common,
                'category'   => 'backend',
                'error'      => $common
                    ? $exception->getFile().' ('.$exception->getLine().')'."\n".$exception->getTraceAsString()
                    : null,
                'account_id' => $user !== null
                    ? $user->id 
                    : null
            ]);
        }

        parent::report($exception);
    }
}
