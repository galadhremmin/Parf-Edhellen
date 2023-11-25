<?php

namespace App\Console;

use App\Models\SystemError;
use Carbon\Carbon;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

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
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->call(function () {
            SystemError::where('created_at', '>=', Carbon::now()->addDays(-90))->delete();
        })->name('Delete SystemError entities older than 90 days.')->daily();
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
