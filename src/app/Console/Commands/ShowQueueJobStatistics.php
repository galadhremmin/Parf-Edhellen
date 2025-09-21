<?php

namespace App\Console\Commands;

use App\Repositories\StatisticsRepository;
use Carbon\Carbon;
use Illuminate\Console\Command;

class ShowQueueJobStatistics extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'queue:show-statistics {--days=7 : Number of days to show statistics for}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Show detailed queue job statistics';

    /**
     * Execute the console command.
     */
    public function handle(StatisticsRepository $statisticsRepository): int
    {
        $days = (int) $this->option('days');
        $startDate = Carbon::now()->subDays($days);
        $endDate = Carbon::now();
        
        $this->info("Queue Job Statistics for the last {$days} days");
        $this->info("Period: {$startDate->format('Y-m-d')} to {$endDate->format('Y-m-d')}");
        
        $stats = $statisticsRepository->getDetailedQueueJobStatistics($startDate, $endDate);
        
        // Show period statistics
        $this->info("\n=== Period Statistics ===");
        $periodStats = $stats['period_stats'];
        $this->table(
            ['Metric', 'Value'],
            [
                ['Total Jobs', $periodStats['total_jobs']],
                ['Successful Jobs', $periodStats['successful_jobs']],
                ['Failed Jobs', $periodStats['failed_jobs']],
                ['Retried Jobs', $periodStats['retried_jobs']],
                ['Success Rate', $periodStats['success_rate'] . '%'],
                ['Avg Execution Time (ms)', round($periodStats['average_execution_time_ms'], 2)],
                ['Avg Attempts', round($periodStats['average_attempts'], 2)],
            ]
        );
        
        // Show statistics by job class
        if ($stats['by_job_class']->isNotEmpty()) {
            $this->info("\n=== Statistics by Job Class ===");
            $jobClassData = $stats['by_job_class']->map(function ($item) {
                return [
                    class_basename($item->job_class),
                    $item->total_count,
                    $item->success_count,
                    $item->failed_count,
                    $item->retry_count,
                    round($item->avg_execution_time_ms, 2),
                    round($item->avg_attempts, 2),
                ];
            })->toArray();
            
            $this->table(
                ['Job Class', 'Total', 'Success', 'Failed', 'Retry', 'Avg Time (ms)', 'Avg Attempts'],
                $jobClassData
            );
        }
        
        // Show statistics by queue
        if ($stats['by_queue']->isNotEmpty()) {
            $this->info("\n=== Statistics by Queue ===");
            $queueData = $stats['by_queue']->map(function ($item) {
                return [
                    $item->queue_name,
                    $item->total_count,
                    $item->success_count,
                    $item->failed_count,
                    $item->retry_count,
                    round($item->avg_execution_time_ms, 2),
                ];
            })->toArray();
            
            $this->table(
                ['Queue Name', 'Total', 'Success', 'Failed', 'Retry', 'Avg Time (ms)'],
                $queueData
            );
        }
        
        // Show most failing jobs
        if ($stats['most_failing_jobs']->isNotEmpty()) {
            $this->info("\n=== Most Failing Jobs ===");
            $failingJobsData = $stats['most_failing_jobs']->map(function ($item) {
                return [
                    class_basename($item->job_class),
                    $item->failure_count,
                    round($item->avg_attempts, 2),
                    $item->last_failure->format('Y-m-d H:i:s'),
                ];
            })->toArray();
            
            $this->table(
                ['Job Class', 'Failure Count', 'Avg Attempts', 'Last Failure'],
                $failingJobsData
            );
        }
        
        // Show slowest jobs
        if ($stats['slowest_jobs']->isNotEmpty()) {
            $this->info("\n=== Slowest Jobs ===");
            $slowestJobsData = $stats['slowest_jobs']->map(function ($item) {
                return [
                    class_basename($item->job_class),
                    $item->execution_count,
                    round($item->avg_execution_time_ms, 2),
                    round($item->max_execution_time_ms, 2),
                    round($item->min_execution_time_ms, 2),
                ];
            })->toArray();
            
            $this->table(
                ['Job Class', 'Executions', 'Avg Time (ms)', 'Max Time (ms)', 'Min Time (ms)'],
                $slowestJobsData
            );
        }
        
        // Show recent failures
        if ($stats['recent_failures']->isNotEmpty()) {
            $this->info("\n=== Recent Failures (Last 20) ===");
            $recentFailuresData = $stats['recent_failures']->map(function ($item) {
                return [
                    class_basename($item->job_class),
                    $item->queue_name,
                    $item->attempts,
                    $item->completed_at->format('Y-m-d H:i:s'),
                    substr($item->error_message, 0, 50) . (strlen($item->error_message) > 50 ? '...' : ''),
                ];
            })->toArray();
            
            $this->table(
                ['Job Class', 'Queue', 'Attempts', 'Failed At', 'Error Message'],
                $recentFailuresData
            );
        }
        
        return Command::SUCCESS;
    }
}
