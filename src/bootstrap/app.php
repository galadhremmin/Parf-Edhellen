<?php

/*
|--------------------------------------------------------------------------
| Create The Application
|--------------------------------------------------------------------------
|
| The first thing we will do is create a new Laravel application instance
| which serves as the "glue" for all the components of Laravel, and is
| the IoC container for the system binding all of the various parts.
|
*/

$app = new Illuminate\Foundation\Application(
    realpath(__DIR__.'/../')
);

/*
|--------------------------------------------------------------------------
| Bind Important Interfaces
|--------------------------------------------------------------------------
|
| Next, we need to bind some important interfaces into the container so
| we will be able to resolve them when needed. The kernels serve the
| incoming requests to this application from both the web and CLI.
|
*/

$app->singleton(
    Illuminate\Contracts\Http\Kernel::class,
    App\Http\Kernel::class
);

$app->singleton(
    App\Interfaces\ISystemLanguageFactory::class,
    function() {
        return new class() implements App\Interfaces\ISystemLanguageFactory {
            private $_language = null;
    
            function language(): App\Models\Language {
                if ($this->_language === null) {
                    $languageName = config('ed.system_language');
                    $this->_language = App\Models\Language::where('name', $languageName)->firstOrFail();
                }
                return $this->_language;
            }
        };
    }
);

$app->singleton(
    Illuminate\Contracts\Console\Kernel::class,
    App\Console\Kernel::class
);

$app->singleton(
    App\Repositories\Interfaces\IAuditTrailRepository::class,
    function ($app) {
        
        $handlerType = ! $app->runningInConsole()  
            ? App\Repositories\AuditTrailRepository::class
            : App\Repositories\Noop\NoopAuditTrailRepository::class;

        return $app->make($handlerType);
    }
);

$app->singleton(
    Illuminate\Contracts\Debug\ExceptionHandler::class,
    function ($app) {
        // DB logging isn't by design enabled in CLI
        $handlerType = ! $app->runningInConsole() && config('ed.system_errors_logging') 
            ? App\Exceptions\DBHandler::class
            : App\Exceptions\Handler::class;

        return $app->make($handlerType);
    }
);

/*
|--------------------------------------------------------------------------
| Return The Application
|--------------------------------------------------------------------------
|
| This script returns the application instance. The instance is given to
| the calling script so we can separate the building of the instances
| from the actual running of the application and sending responses.
|
*/

return $app;
