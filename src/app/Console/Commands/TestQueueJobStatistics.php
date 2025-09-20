<?php

namespace App\Console\Commands;

use App\Jobs\ProcessSearchIndexCreation;
use App\Models\Language;
use App\Models\Word;
use App\Repositories\QueueJobStatisticRepository;
use App\Repositories\StatisticsRepository;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Queue;

class TestQueueJobStatistics extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'queue:test-statistics {--count=5 : Number of test jobs to dispatch}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test queue job statistics by dispatching test jobs and showing statistics';

    /**
     * Execute the console command.
     */
    public function handle(QueueJobStatisticRepository $statisticRepository, StatisticsRepository $statisticsRepository): int
    {
        $count = (int) $this->option('count');
        
        $this->info("Testing queue job statistics with {$count} test jobs...");
        
        // Get initial statistics
        $this->info("\n=== Initial Statistics ===");
        $this->displayQueueStatistics($statisticsRepository);
        
        // Dispatch test jobs
        $this->info("\n=== Dispatching Test Jobs ===");
        for ($i = 0; $i < $count; $i++) {
            // Create a simple test job (using existing job class)
            $word = Word::first();
            $language = Language::first();
            
            if ($word && $language) {
                ProcessSearchIndexCreation::dispatch($word, $word, $language);
                $this->line("Dispatched test job " . ($i + 1) . "/{$count}");
            } else {
                $this->warn("No words or languages found in database. Skipping job dispatch.");
                break;
            }
        }
        
        $this->info("\n=== Processing Jobs ===");
        $this->info("Please run 'php artisan queue:work --once' to process the jobs, then run this command again to see updated statistics.");
        
        // Show current statistics
        $this->info("\n=== Current Statistics ===");
        $this->displayQueueStatistics($statisticsRepository);
        
        return Command::SUCCESS;
    }
    
    private function displayQueueStatistics(StatisticsRepository $statisticsRepository): void
    {
        $stats = $statisticsRepository->getQueueJobStatistics();
        
        $this->table(
            ['Metric', 'Value'],
            [
                ['Total Jobs Today', $stats['total_jobs_today']],
                ['Successful Jobs Today', $stats['successful_jobs_today']],
                ['Failed Jobs Today', $stats['failed_jobs_today']],
                ['Success Rate Today', $stats['success_rate_today'] . '%'],
                ['Avg Execution Time (ms)', round($stats['avg_execution_time_ms'], 2)],
                ['Daily Change', $stats['daily_change_percent'] . '%'],
            ]
        );
    }
}
