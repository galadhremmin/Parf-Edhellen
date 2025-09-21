<?php

namespace App\Console\Commands;

use App\Repositories\QueueJobStatisticRepository;
use Illuminate\Console\Command;

class CleanupQueueJobStatistics extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'queue:cleanup-statistics {--days=90 : Number of days to keep statistics}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up old queue job statistics records';

    /**
     * Execute the console command.
     */
    public function handle(QueueJobStatisticRepository $statisticRepository): int
    {
        $days = (int) $this->option('days');
        
        $this->info("Cleaning up queue job statistics older than {$days} days...");
        
        $deletedCount = $statisticRepository->cleanupOldStatistics($days);
        
        $this->info("Deleted {$deletedCount} old statistics records.");
        
        return Command::SUCCESS;
    }
}
