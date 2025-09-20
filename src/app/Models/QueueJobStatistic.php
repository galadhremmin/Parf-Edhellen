<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

class QueueJobStatistic extends ModelBase
{
    protected $table = 'queue_job_statistics';

    protected $fillable = [
        'job_class',
        'queue_name',
        'status',
        'execution_time_ms',
        'attempts',
        'error_message',
        'connection',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'execution_time_ms' => 'integer',
        'attempts' => 'integer',
    ];

    // Status constants
    public const STATUS_SUCCESS = 'success';
    public const STATUS_FAILED = 'failed';
    public const STATUS_RETRY = 'retry';

    /**
     * Scope for successful jobs
     */
    public function scopeSuccessful(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_SUCCESS);
    }

    /**
     * Scope for failed jobs
     */
    public function scopeFailed(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_FAILED);
    }

    /**
     * Scope for retried jobs
     */
    public function scopeRetried(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_RETRY);
    }

    /**
     * Scope for a specific job class
     */
    public function scopeForJobClass(Builder $query, string $jobClass): Builder
    {
        return $query->where('job_class', $jobClass);
    }

    /**
     * Scope for a specific queue
     */
    public function scopeForQueue(Builder $query, string $queueName): Builder
    {
        return $query->where('queue_name', $queueName);
    }

    /**
     * Scope for jobs completed within a date range
     */
    public function scopeCompletedBetween(Builder $query, Carbon $startDate, Carbon $endDate): Builder
    {
        return $query->whereBetween('completed_at', [$startDate, $endDate]);
    }

    /**
     * Scope for jobs completed today
     */
    public function scopeCompletedToday(Builder $query): Builder
    {
        return $query->whereDate('completed_at', Carbon::today());
    }

    /**
     * Scope for jobs completed this week
     */
    public function scopeCompletedThisWeek(Builder $query): Builder
    {
        return $query->whereBetween('completed_at', [
            Carbon::now()->startOfWeek(),
            Carbon::now()->endOfWeek()
        ]);
    }

    /**
     * Scope for jobs completed this month
     */
    public function scopeCompletedThisMonth(Builder $query): Builder
    {
        return $query->whereBetween('completed_at', [
            Carbon::now()->startOfMonth(),
            Carbon::now()->endOfMonth()
        ]);
    }

    /**
     * Get the job class name without namespace
     */
    public function getJobClassNameAttribute(): string
    {
        return class_basename($this->job_class);
    }

    /**
     * Get execution time in seconds
     */
    public function getExecutionTimeSecondsAttribute(): ?float
    {
        return $this->execution_time_ms ? $this->execution_time_ms / 1000 : null;
    }

    /**
     * Check if the job was successful
     */
    public function isSuccessful(): bool
    {
        return $this->status === self::STATUS_SUCCESS;
    }

    /**
     * Check if the job failed
     */
    public function isFailed(): bool
    {
        return $this->status === self::STATUS_FAILED;
    }

    /**
     * Check if the job was retried
     */
    public function isRetried(): bool
    {
        return $this->status === self::STATUS_RETRY;
    }
}
