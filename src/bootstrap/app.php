<?php

use App\Http\Middleware\CarbonLocale;
use App\Http\Middleware\EnsureHttpsAndWww;
use App\Http\Middleware\LayoutDataLoader;
use App\Http\Middleware\TrimStrings;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Foundation\Http\Middleware\CheckForMaintenanceMode;
use Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull;
use Illuminate\Foundation\Http\Middleware\ValidatePostSize;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->append(EnsureHttpsAndWww::class);
        $middleware->append(CheckForMaintenanceMode::class);
        $middleware->append(ValidatePostSize::class);
        $middleware->append(TrimStrings::class);
        $middleware->append(ConvertEmptyStringsToNull::class);
        $middleware->append(CarbonLocale::class);
        $middleware->append(LayoutDataLoader::class);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
