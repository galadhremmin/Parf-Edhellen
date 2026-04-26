<?php

use App\Http\Middleware\CarbonLocale;
use App\Http\Middleware\CheckRole;
use App\Http\Middleware\CustomValidateCsrfToken;
use App\Http\Middleware\InvalidUserGate;
use App\Http\Middleware\IpGate;
use App\Http\Middleware\LogExpensiveRequests;
use App\Http\Middleware\RedirectIfAuthenticated;
use App\Http\Middleware\SafeSubstituteBindings;
use App\Http\Middleware\TrimStrings;
use App\Models\FailedJob;
use App\Repositories\SystemErrorRepository;
use App\Security\WebAuthnService;
use Carbon\Carbon;
use Illuminate\Auth\Middleware\AuthenticateWithBasicAuth;
use Illuminate\Auth\Middleware\Authorize;
use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Auth\Middleware\EnsureEmailIsVerified;
use Illuminate\Console\Scheduling\Schedule;
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
use Illuminate\Support\Stringable;
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
            'log.expensive' => LogExpensiveRequests::class,
        ];
        $middleware->alias($routeMiddleware);

        $middleware->group('web', [
            EncryptCookies::class,
            AddQueuedCookiesToResponse::class,
            StartSession::class,
            ShareErrorsFromSession::class,
            CustomValidateCsrfToken::class,
            ValidatePostSize::class,
            TrimStrings::class,
            ConvertEmptyStringsToNull::class,
            InvalidUserGate::class,
            CarbonLocale::class,
            CheckForMaintenanceMode::class,
            SafeSubstituteBindings::class,
        ]);

        $middleware->group('api', [
            EncryptCookies::class,
            AddQueuedCookiesToResponse::class,
            StartSession::class,
            ShareErrorsFromSession::class,
            IpGate::class,
            InvalidUserGate::class,
            ValidatePostSize::class,
            TrimStrings::class,
            ConvertEmptyStringsToNull::class,
            SafeSubstituteBindings::class,
            LogExpensiveRequests::class,
        ]);
    })
    ->withSchedule(function (Schedule $schedule) {
        $schedule->call(function (SystemErrorRepository $systemErrorRepository) {
            $systemErrorRepository->deleteOlderThan(Carbon::now()->addDays(-90));
        }) //
            ->onFailure(function (Stringable $output, SystemErrorRepository $systemErrorRepository) {
                $systemErrorRepository->saveException(new Exception(
                    sprintf('Failed to delete old SystemErrors. Output: %s', $output)
                ), 'scheduler');
            }) //
            ->name('Delete SystemError entities older than 90 days.') //
            ->hourly();

        $schedule->call(function () {
            FailedJob::where('failed_at', '<=', Carbon::now()->addDays(-90))->delete();
        }) //
            ->onFailure(function (Stringable $output, SystemErrorRepository $systemErrorRepository) {
                $systemErrorRepository->saveException(new Exception(
                    sprintf('Failed to delete old failed jobs. Output: %s', $output)
                ), 'scheduler');
            }) //
            ->name('Delete failed jobs entities older than 90 days.') //
            ->monthly();

        $schedule->call(function (WebAuthnService $webAuthnService) {
            $webAuthnService->cleanupExpiredSessions();
        }) //
            ->onFailure(function (Stringable $output, SystemErrorRepository $systemErrorRepository) {
                $systemErrorRepository->saveException(new Exception(
                    sprintf('Failed to cleanup expired WebAuthn sessions. Output: %s', $output)
                ), 'scheduler');
            }) //
            ->name('Cleanup expired WebAuthn sessions.') //
            ->hourly();

        $schedule->command('ed:prune-search-view-events') //
            ->daily() //
            ->name('Prune search view events older than retention period');

        $tweetCron = config('ed.tweet_word_of_day_cron', '');
        if ($tweetCron !== '') {
            $schedule->command('ed:tweet-word-of-the-day') //
                ->cron($tweetCron) //
                ->onFailure(function (Stringable $output, SystemErrorRepository $systemErrorRepository) {
                    $systemErrorRepository->saveException(new Exception(
                        sprintf('Failed to tweet word of the day. Output: %s', $output)
                    ), 'scheduler');
                }) //
                ->name('Tweet word of the day');
        }

        $schedule->command('ed:generate-daily-crosswords') //
            ->weekly() //
            ->onFailure(function (Stringable $output, SystemErrorRepository $systemErrorRepository) {
                $systemErrorRepository->saveException(new Exception(
                    sprintf('Failed to generate daily crosswords. Output: %s', $output)
                ), 'scheduler');
            }) //
            ->name('Generate daily crossword puzzles for enabled languages');

        $schedule->command('queue:cleanup-statistics', ['--days' => 30]) //
            ->daily() //
            ->onFailure(function (Stringable $output, SystemErrorRepository $systemErrorRepository) {
                $systemErrorRepository->saveException(new Exception(
                    sprintf('Failed to clean up queue job statistics. Output: %s', $output)
                ), 'scheduler');
            }) //
            ->name('Delete queue job statistics older than 30 days (L30)');
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
