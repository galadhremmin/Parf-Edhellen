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

$statsRepository = new StatisticsRepository();

// Get basic queue job statistics (included in global stats)
$globalStats = $statsRepository->getGlobalStatistics();
$queueStats = $globalStats['queueJobStats'];

// Get detailed statistics for a specific period
$startDate = Carbon::now()->subDays(7);
$endDate = Carbon::now();
$detailedStats = $statsRepository->getDetailedQueueJobStatistics($startDate, $endDate);
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
- `status`: 'success', 'failed', or 'retry'
- `execution_time_ms`: Execution time in milliseconds
- `attempts`: Number of attempts
- `error_message`: Error message for failed jobs
- `connection`: Queue connection name
- `started_at`: When the job started processing
- `completed_at`: When the job completed
- `created_at`/`updated_at`: Standard timestamps

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
```

### Integration with Existing Statistics

The queue job statistics are automatically included in your existing `StatisticsRepository::getGlobalStatistics()` method under the `queueJobStats` key.

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
