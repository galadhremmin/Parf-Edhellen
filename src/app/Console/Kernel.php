<?php

namespace App\Console;

use App\Models\FailedJob;
use App\Repositories\SystemErrorRepository;
use Carbon\Carbon;
use Exception;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Stringable;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        Commands\ImportDictionaryCommand::class,
        Commands\ImportEldamoCommand::class,
        Commands\ImportProfileFeatureBackground::class,
        Commands\RefreshSearchIndexFromKeywordsCommand::class,
        Commands\RefreshSearchIndexFromGlossesCommand::class,
        Commands\RefreshDiscussIndexesCommand::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
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
    }

    /**
     * Register the Closure based commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        require base_path('routes/console.php');
    }
}
