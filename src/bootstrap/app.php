<?php

use App\Http\Middleware\CarbonLocale;
use App\Http\Middleware\EnsureHttpsAndWww;
use App\Http\Middleware\IpGate;
use App\Http\Middleware\LayoutDataLoader;
use App\Http\Middleware\TrimStrings;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Foundation\Http\Middleware\CheckForMaintenanceMode;
use Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull;
use Illuminate\Foundation\Http\Middleware\ValidatePostSize;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Session\Middleware\StartSession;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $routeMiddleware = [
            'auth' => \Illuminate\Auth\Middleware\Authenticate::class,
            'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
            'bindings' => \Illuminate\Routing\Middleware\SubstituteBindings::class,
            'can' => \Illuminate\Auth\Middleware\Authorize::class,
            'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
            'signed' => \App\Http\Middleware\ValidateSignature::class,
            'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
            'verified' => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,
            'auth.require-role' => \App\Http\Middleware\CheckRole::class,  
        ];
        $middleware->alias($routeMiddleware);

        $middleware->append(EnsureHttpsAndWww::class)
            ->append(CheckForMaintenanceMode::class)
            ->append(IpGate::class)
            ->append(ValidatePostSize::class)
            ->append(TrimStrings::class)
            ->append(ConvertEmptyStringsToNull::class)
            ->append(StartSession::class)
            ->append(VerifyCsrfToken::class)
            ->append(CarbonLocale::class)
            ->append(LayoutDataLoader::class);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
