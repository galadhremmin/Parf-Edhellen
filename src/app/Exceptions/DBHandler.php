<?php

namespace App\Exceptions;

use Exception;

use Illuminate\Contracts\Container\Container;
use App\Repositories\SystemErrorRepository;

class DBHandler extends Handler
{
    /**
     * @var SystemErrorRepository
     */
    private $_systemErrorRepository;

    public function __construct(Container $container, SystemErrorRepository $systemErrorRepository)
    {
        $this->_systemErrorRepository = $systemErrorRepository;
        parent::__construct($container);
    }

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Exception  $exception
     * @return void
     */
    public function report(\Throwable $exception)
    {
        foreach ($this->dontReport as $type) {
            if ($exception instanceof $type) {
                return;
            }
        }

        $this->_systemErrorRepository->saveException($exception);
        parent::report($exception);
    }
}
