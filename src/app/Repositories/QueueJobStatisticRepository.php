<?php

namespace App\Repositories;

use App\Models\QueueJobStatistic;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class QueueJobStatisticRepository
{
    /**
     * Create a new queue job statistic record
     */
    public function create(array $data): QueueJobStatistic
    {
        return QueueJobStatistic::create($data);
    }

    /**
     * Get statistics for a specific time period
     */
    public function getStatisticsForPeriod(Carbon $startDate, Carbon $endDate): array
    {
        $stats = QueueJobStatistic::completedBetween($startDate, $endDate)
            ->select([
                'status',
                DB::raw('COUNT(*) as count'),
                DB::raw('AVG(execution_time_ms) as avg_execution_time_ms'),
                DB::raw('MIN(execution_time_ms) as min_execution_time_ms'),
                DB::raw('MAX(execution_time_ms) as max_execution_time_ms'),
                DB::raw('AVG(attempts) as avg_attempts'),
            ])
            ->groupBy('status')
            ->get()
            ->keyBy('status');

        return [
            'total_jobs' => $stats->sum('count'),
            'successful_jobs' => $stats->get(QueueJobStatistic::STATUS_SUCCESS)?->count ?? 0,
            'failed_jobs' => $stats->get(QueueJobStatistic::STATUS_FAILED)?->count ?? 0,
            'retried_jobs' => $stats->get(QueueJobStatistic::STATUS_RETRY)?->count ?? 0,
            'success_rate' => $this->calculateSuccessRate($stats),
            'average_execution_time_ms' => $stats->avg('avg_execution_time_ms'),
            'average_attempts' => $stats->avg('avg_attempts'),
            'detailed_stats' => $stats->toArray(),
        ];
    }

    /**
     * Get statistics by job class
     */
    public function getStatisticsByJobClass(Carbon $startDate, Carbon $endDate): Collection
    {
        return QueueJobStatistic::completedBetween($startDate, $endDate)
            ->select([
                'job_class',
                DB::raw('COUNT(*) as total_count'),
                DB::raw('SUM(CASE WHEN status = "' . QueueJobStatistic::STATUS_SUCCESS . '" THEN 1 ELSE 0 END) as success_count'),
                DB::raw('SUM(CASE WHEN status = "' . QueueJobStatistic::STATUS_FAILED . '" THEN 1 ELSE 0 END) as failed_count'),
                DB::raw('SUM(CASE WHEN status = "' . QueueJobStatistic::STATUS_RETRY . '" THEN 1 ELSE 0 END) as retry_count'),
                DB::raw('AVG(execution_time_ms) as avg_execution_time_ms'),
                DB::raw('AVG(attempts) as avg_attempts'),
            ])
            ->groupBy('job_class')
            ->orderBy('total_count', 'desc')
            ->get();
    }

    /**
     * Get statistics by queue name
     */
    public function getStatisticsByQueue(Carbon $startDate, Carbon $endDate): Collection
    {
        return QueueJobStatistic::completedBetween($startDate, $endDate)
            ->select([
                'queue_name',
                DB::raw('COUNT(*) as total_count'),
                DB::raw('SUM(CASE WHEN status = "' . QueueJobStatistic::STATUS_SUCCESS . '" THEN 1 ELSE 0 END) as success_count'),
                DB::raw('SUM(CASE WHEN status = "' . QueueJobStatistic::STATUS_FAILED . '" THEN 1 ELSE 0 END) as failed_count'),
                DB::raw('SUM(CASE WHEN status = "' . QueueJobStatistic::STATUS_RETRY . '" THEN 1 ELSE 0 END) as retry_count'),
                DB::raw('AVG(execution_time_ms) as avg_execution_time_ms'),
            ])
            ->groupBy('queue_name')
            ->orderBy('total_count', 'desc')
            ->get();
    }

    /**
     * Get hourly statistics for a specific date
     */
    public function getHourlyStatistics(Carbon $date): Collection
    {
        return QueueJobStatistic::whereDate('completed_at', $date)
            ->select([
                DB::raw('HOUR(completed_at) as hour'),
                DB::raw('COUNT(*) as total_count'),
                DB::raw('SUM(CASE WHEN status = "' . QueueJobStatistic::STATUS_SUCCESS . '" THEN 1 ELSE 0 END) as success_count'),
                DB::raw('SUM(CASE WHEN status = "' . QueueJobStatistic::STATUS_FAILED . '" THEN 1 ELSE 0 END) as failed_count'),
                DB::raw('AVG(execution_time_ms) as avg_execution_time_ms'),
            ])
            ->groupBy(DB::raw('HOUR(completed_at)'))
            ->orderBy('hour')
            ->get();
    }

    /**
     * Get daily statistics for a date range
     */
    public function getDailyStatistics(Carbon $startDate, Carbon $endDate): Collection
    {
        return QueueJobStatistic::completedBetween($startDate, $endDate)
            ->select([
                DB::raw('DATE(completed_at) as date'),
                DB::raw('COUNT(*) as total_count'),
                DB::raw('SUM(CASE WHEN status = "' . QueueJobStatistic::STATUS_SUCCESS . '" THEN 1 ELSE 0 END) as success_count'),
                DB::raw('SUM(CASE WHEN status = "' . QueueJobStatistic::STATUS_FAILED . '" THEN 1 ELSE 0 END) as failed_count'),
                DB::raw('AVG(execution_time_ms) as avg_execution_time_ms'),
            ])
            ->groupBy(DB::raw('DATE(completed_at)'))
            ->orderBy('date')
            ->get();
    }

    /**
     * Get the most frequently failing jobs
     */
    public function getMostFailingJobs(int $limit = 10): Collection
    {
        return QueueJobStatistic::failed()
            ->select([
                'job_class',
                DB::raw('COUNT(*) as failure_count'),
                DB::raw('AVG(attempts) as avg_attempts'),
                DB::raw('MAX(completed_at) as last_failure'),
            ])
            ->groupBy('job_class')
            ->orderBy('failure_count', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get the slowest jobs by average execution time
     */
    public function getSlowestJobs(int $limit = 10): Collection
    {
        return QueueJobStatistic::successful()
            ->select([
                'job_class',
                DB::raw('COUNT(*) as execution_count'),
                DB::raw('AVG(execution_time_ms) as avg_execution_time_ms'),
                DB::raw('MAX(execution_time_ms) as max_execution_time_ms'),
                DB::raw('MIN(execution_time_ms) as min_execution_time_ms'),
            ])
            ->whereNotNull('execution_time_ms')
            ->groupBy('job_class')
            ->orderBy('avg_execution_time_ms', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get recent job failures with error details
     */
    public function getRecentFailures(int $limit = 50): Collection
    {
        return QueueJobStatistic::failed()
            ->select([
                'job_class',
                'queue_name',
                'error_message',
                'attempts',
                'completed_at',
            ])
            ->orderBy('completed_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get queue health metrics
     */
    public function getQueueHealthMetrics(): array
    {
        $today = Carbon::today();
        $yesterday = Carbon::yesterday();
        $thisWeek = Carbon::now()->startOfWeek();
        $lastWeek = Carbon::now()->subWeek()->startOfWeek();

        $todayStats = $this->getStatisticsForPeriod($today, Carbon::now());
        $yesterdayStats = $this->getStatisticsForPeriod($yesterday, $yesterday->copy()->endOfDay());
        $thisWeekStats = $this->getStatisticsForPeriod($thisWeek, Carbon::now());
        $lastWeekStats = $this->getStatisticsForPeriod($lastWeek, $lastWeek->copy()->addWeek()->subSecond());

        return [
            'today' => $todayStats,
            'yesterday' => $yesterdayStats,
            'this_week' => $thisWeekStats,
            'last_week' => $lastWeekStats,
            'trends' => [
                'daily_change' => $this->calculatePercentageChange($yesterdayStats['total_jobs'], $todayStats['total_jobs']),
                'weekly_change' => $this->calculatePercentageChange($lastWeekStats['total_jobs'], $thisWeekStats['total_jobs']),
                'success_rate_change' => $this->calculatePercentageChange($yesterdayStats['success_rate'], $todayStats['success_rate']),
            ],
        ];
    }

    /**
     * Clean up old statistics (older than specified days)
     */
    public function cleanupOldStatistics(int $daysToKeep = 90): int
    {
        $cutoffDate = Carbon::now()->subDays($daysToKeep);
        return QueueJobStatistic::where('completed_at', '<', $cutoffDate)->delete();
    }

    /**
     * Calculate success rate percentage
     */
    private function calculateSuccessRate($stats): float
    {
        $total = $stats->sum('count');
        if ($total === 0) {
            return 0.0;
        }

        $successful = $stats->get(QueueJobStatistic::STATUS_SUCCESS)?->count ?? 0;
        return round(($successful / $total) * 100, 2);
    }

    /**
     * Calculate percentage change between two values
     */
    private function calculatePercentageChange(float $oldValue, float $newValue): float
    {
        if ($oldValue === 0) {
            return $newValue > 0 ? 100.0 : 0.0;
        }

        return round((($newValue - $oldValue) / $oldValue) * 100, 2);
    }
}
