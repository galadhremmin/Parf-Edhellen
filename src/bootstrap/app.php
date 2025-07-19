<?php

use App\Http\Middleware\CarbonLocale;
use App\Http\Middleware\CheckRole;
use App\Http\Middleware\CustomValidateCsrfToken;
use App\Http\Middleware\EnsureHttpsAndWww;
use App\Http\Middleware\InvalidUserGate;
use App\Http\Middleware\IpGate;
use App\Http\Middleware\LayoutDataLoader;
use App\Http\Middleware\RedirectIfAuthenticated;
use App\Http\Middleware\SafeSubstituteBindings;
use App\Http\Middleware\TrimStrings;
use Illuminate\Auth\Middleware\AuthenticateWithBasicAuth;
use Illuminate\Auth\Middleware\Authorize;
use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Auth\Middleware\EnsureEmailIsVerified;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Foundation\Http\Middleware\CheckForMaintenanceMode;
use Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull;
use Illuminate\Foundation\Http\Middleware\ValidatePostSize;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Routing\Middleware\ValidateSignature;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $routeMiddleware = [
            'auth' => Authenticate::class,
            'auth.basic' => AuthenticateWithBasicAuth::class,
            'bindings' => SafeSubstituteBindings::class,
            'can' => Authorize::class,
            'guest' => RedirectIfAuthenticated::class,
            'signed' => ValidateSignature::class,
            'throttle' => ThrottleRequests::class,
            'verified' => EnsureEmailIsVerified::class,
            'auth.require-role' => CheckRole::class,  
        ];
        $middleware->alias($routeMiddleware);

        $middleware->group('web', [
            AddQueuedCookiesToResponse::class,
            EnsureHttpsAndWww::class,
            StartSession::class,
            ShareErrorsFromSession::class,
            ValidatePostSize::class,
            TrimStrings::class,
            ConvertEmptyStringsToNull::class,
            CustomValidateCsrfToken::class,
            InvalidUserGate::class,
            CarbonLocale::class,
            LayoutDataLoader::class,
            CheckForMaintenanceMode::class,
            SafeSubstituteBindings::class,
        ]);

        $middleware->group('api', [
            IpGate::class,
            EncryptCookies::class,
            AddQueuedCookiesToResponse::class,
            StartSession::class,
            ShareErrorsFromSession::class,
            InvalidUserGate::class,
            ValidatePostSize::class,
            TrimStrings::class,
            ConvertEmptyStringsToNull::class,
            SafeSubstituteBindings::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
