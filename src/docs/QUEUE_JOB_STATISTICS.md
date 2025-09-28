# Queue Job Statistics System

This system automatically tracks and stores statistics for all queue jobs in your Laravel application, including both successful and failed jobs.

## Features

- **Automatic Tracking**: All queue jobs are automatically tracked without any code changes to existing jobs
- **Detailed Metrics**: Execution time, attempt count, error messages, and more
- **Historical Data**: Store and query statistics over time periods
- **Performance Monitoring**: Identify slow jobs and frequently failing jobs
- **Health Metrics**: Track success rates and trends over time

## Installation

1. **Run the migration** to create the statistics table:
   ```bash
   php artisan migrate
   ```

2. **The system is automatically enabled** - no additional configuration needed!

## Usage

### Viewing Statistics

#### Basic Statistics
```php
use App\Repositories\StatisticsRepository;
use App\Repositories\QueueJobStatisticRepository;

$statsRepository = new StatisticsRepository();
$queueStatsRepository = new QueueJobStatisticRepository();

// Get basic global statistics
$globalStats = $statsRepository->getGlobalStatistics();

// Get detailed queue job statistics for a specific period
$startDate = Carbon::now()->subDays(7);
$endDate = Carbon::now();
$detailedStats = $statsRepository->getDetailedQueueJobStatistics($startDate, $endDate);

// Or get queue job statistics directly
$queueStats = $queueStatsRepository->getStatisticsForPeriod($startDate, $endDate);
```

#### Using Console Commands

**Show current statistics:**
```bash
php artisan queue:show-statistics --days=7
```

**Test the system:**
```bash
php artisan queue:test-statistics --count=10
```

**Clean up old statistics:**
```bash
php artisan queue:cleanup-statistics --days=90
```

**Clean up stuck/active jobs:**
```bash
php artisan queue:cleanup-active-jobs --timeout=120
php artisan queue:cleanup-active-jobs --timeout=60 --dry-run
```

### Available Statistics

The system tracks the following metrics:

- **Job Status**: Success, Failed, Retry
- **Execution Time**: In milliseconds
- **Attempt Count**: Number of attempts before completion/failure
- **Error Messages**: For failed jobs
- **Queue Information**: Queue name and connection
- **Timestamps**: Start time, completion time
- **Job Class**: The specific job class that was executed

### Database Schema

The `queue_job_statistics` table includes:

- `job_class`: The full class name of the job
- `queue_name`: The queue the job was processed on
- `status`: 'success', 'failed', 'retry', 'processing', or 'timeout'
- `execution_time_ms`: Execution time in milliseconds
- `attempts`: Number of attempts
- `error_message`: Error message for failed jobs
- `connection`: Queue connection name
- `started_at`: When the job started processing
- `completed_at`: When the job completed
- `is_active`: Whether the job is currently running (boolean)
- `created_at`/`updated_at`: Standard timestamps

**Key Features:**
- **Database Persistence**: Job start times are stored in the database, ensuring they persist across queue worker restarts and multiple worker instances
- **Active Job Tracking**: The `is_active` field allows tracking of currently running jobs
- **Timeout Detection**: Jobs can be marked as 'timeout' if they run too long
- **Multi-Worker Support**: Works correctly with multiple queue workers running simultaneously

### Repository Methods

The `QueueJobStatisticRepository` provides these methods:

- `getStatisticsForPeriod($startDate, $endDate)`: Overall statistics for a period
- `getStatisticsByJobClass($startDate, $endDate)`: Statistics grouped by job class
- `getStatisticsByQueue($startDate, $endDate)`: Statistics grouped by queue
- `getHourlyStatistics($date)`: Hourly breakdown for a specific date
- `getDailyStatistics($startDate, $endDate)`: Daily breakdown for a period
- `getMostFailingJobs($limit)`: Jobs that fail most frequently
- `getSlowestJobs($limit)`: Jobs with longest execution times
- `getRecentFailures($limit)`: Recent job failures with error details
- `getQueueHealthMetrics()`: Health metrics and trends
- `cleanupOldStatistics($daysToKeep)`: Remove old statistics
- `findActiveJob($jobClass, $queueName, $completedAt)`: Find active job for completion tracking
- `updateJobCompletion($job, $data)`: Update job completion data
- `getStuckJobs($timeoutMinutes)`: Get jobs that have been running too long
- `cleanupStuckJobs($timeoutMinutes)`: Mark stuck jobs as timed out
- `getActiveJobsCount()`: Get count of currently active jobs

### Model Usage

```php
use App\Models\QueueJobStatistic;

// Get all successful jobs from today
$successfulJobs = QueueJobStatistic::successful()
    ->completedToday()
    ->get();

// Get failed jobs for a specific job class
$failedJobs = QueueJobStatistic::failed()
    ->forJobClass(ProcessSearchIndexCreation::class)
    ->get();

// Get jobs with execution time over 5 seconds
$slowJobs = QueueJobStatistic::where('execution_time_ms', '>', 5000)
    ->get();

// Get jobs completed this week
$thisWeekJobs = QueueJobStatistic::completedThisWeek()->get();

// Get jobs completed this month
$thisMonthJobs = QueueJobStatistic::completedThisMonth()->get();

// Get jobs for a specific queue
$defaultQueueJobs = QueueJobStatistic::forQueue('default')->get();

// Access computed attributes
$statistic = QueueJobStatistic::first();
$jobClassName = $statistic->job_class_name; // Gets class name without namespace
$executionTimeSeconds = $statistic->execution_time_seconds; // Gets time in seconds

// Get active jobs
$activeJobs = QueueJobStatistic::active()->get();

// Get stuck jobs (running too long)
$stuckJobs = QueueJobStatistic::stuck(60)->get(); // 60 minutes timeout

// Check if job is active or stuck
$isActive = $statistic->isActive();
$isStuck = $statistic->isStuck(60); // 60 minutes timeout
```

### Integration with Existing Statistics

The queue job statistics are available through the `StatisticsRepository::getDetailedQueueJobStatistics()` method, which provides comprehensive queue job analytics alongside your existing statistics infrastructure.

### Performance Considerations

- **Automatic Cleanup**: Use the cleanup command to remove old statistics
- **Indexed Queries**: The table is properly indexed for common query patterns
- **Minimal Storage**: Only essential job metadata is stored to minimize storage costs
- **Error Handling**: Statistics recording won't break job processing if errors occur

### Monitoring and Alerts

You can use the statistics to:

1. **Monitor Success Rates**: Set up alerts for low success rates
2. **Track Performance**: Monitor average execution times
3. **Identify Issues**: Find frequently failing jobs
4. **Capacity Planning**: Understand job volume and patterns

### Example Dashboard Queries

```php
// Get today's success rate
$todayStats = QueueJobStatistic::completedToday()
    ->selectRaw('
        COUNT(*) as total,
        SUM(CASE WHEN status = "success" THEN 1 ELSE 0 END) as successful
    ')
    ->first();

$successRate = $todayStats->total > 0 
    ? ($todayStats->successful / $todayStats->total) * 100 
    : 0;

// Get jobs that are getting slower over time
$trendingSlowJobs = QueueJobStatistic::successful()
    ->where('completed_at', '>=', Carbon::now()->subDays(30))
    ->selectRaw('
        job_class,
        AVG(execution_time_ms) as avg_time,
        COUNT(*) as execution_count
    ')
    ->groupBy('job_class')
    ->having('execution_count', '>', 10)
    ->orderBy('avg_time', 'desc')
    ->limit(10)
    ->get();
```

## Troubleshooting

### Statistics Not Being Recorded

1. Check that the migration has been run
2. Verify that jobs are actually being processed (not just dispatched)
3. Check the Laravel logs for any errors in the event listener

### Performance Issues

1. Run the cleanup command to remove old statistics
2. Check that database indexes are properly created
3. Consider archiving very old statistics to a separate table

### Missing Data

The system only tracks jobs that are actually processed by the queue worker. Jobs that are dispatched but never processed won't appear in statistics.
