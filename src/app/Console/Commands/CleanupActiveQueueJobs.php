<?php

namespace App\Console\Commands;

use App\Repositories\QueueJobStatisticRepository;
use Illuminate\Console\Command;

class CleanupActiveQueueJobs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'queue:cleanup-active-jobs 
                            {--timeout=120 : Timeout in minutes for considering jobs as stuck}
                            {--dry-run : Show what would be cleaned up without making changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up stuck or crashed queue jobs by marking them as timed out';

    /**
     * Execute the console command.
     */
    public function handle(QueueJobStatisticRepository $statisticRepository): int
    {
        $timeoutMinutes = (int) $this->option('timeout');
        $dryRun = $this->option('dry-run');

        $this->info("Checking for stuck jobs older than {$timeoutMinutes} minutes...");

        // Get stuck jobs
        $stuckJobs = $statisticRepository->getStuckJobs($timeoutMinutes);

        if ($stuckJobs->isEmpty()) {
            $this->info('No stuck jobs found.');
            return 0;
        }

        $this->info("Found {$stuckJobs->count()} stuck job(s):");
        
        // Display stuck jobs
        $headers = ['ID', 'Job Class', 'Queue', 'Started At', 'Duration'];
        $rows = [];
        
        foreach ($stuckJobs as $job) {
            $duration = $job->started_at ? $job->started_at->diffForHumans() : 'Unknown';
            $rows[] = [
                $job->id,
                class_basename($job->job_class),
                $job->queue_name,
                $job->started_at ? $job->started_at->format('Y-m-d H:i:s') : 'Unknown',
                $duration
            ];
        }
        
        $this->table($headers, $rows);

        if ($dryRun) {
            $this->info('Dry run mode - no changes were made.');
            return 0;
        }

        // Confirm cleanup
        if (!$this->confirm("Do you want to mark these jobs as timed out?")) {
            $this->info('Cleanup cancelled.');
            return 0;
        }

        // Perform cleanup
        $cleanedCount = $statisticRepository->cleanupStuckJobs($timeoutMinutes);

        $this->info("Successfully marked {$cleanedCount} job(s) as timed out.");

        // Show summary
        $activeCount = $statisticRepository->getActiveJobsCount();
        $this->info("Active jobs remaining: {$activeCount}");

        return 0;
    }
}
