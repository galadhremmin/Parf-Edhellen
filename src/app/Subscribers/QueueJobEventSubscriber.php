<?php

namespace App\Subscribers;

use App\Models\QueueJobStatistic;
use App\Repositories\QueueJobStatisticRepository;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Support\Facades\Log;

class QueueJobEventSubscriber
{
    private QueueJobStatisticRepository $statisticRepository;
    private array $jobStartTimes = [];

    public function __construct(QueueJobStatisticRepository $statisticRepository)
    {
        $this->statisticRepository = $statisticRepository;
    }

    /**
     * Register the event listeners
     */
    public function subscribe()
    {
        return [
            JobProcessing::class => 'handleJobProcessing',
            JobProcessed::class => 'handleJobProcessed',
            JobFailed::class => 'handleJobFailed',
        ];
    }

    /**
     * Handle job processing event (job started)
     */
    public function handleJobProcessing(JobProcessing $event): void
    {
        Log::info('QueueJobEventListener: Job processing started', [
            'job_id' => $this->getJobId($event),
            'job_class' => $this->extractJobData($event)['class'],
            'queue' => $this->extractJobData($event)['queue'],
        ]);
        
        $jobId = $this->getJobId($event);
        $this->jobStartTimes[$jobId] = microtime(true);
    }

    /**
     * Handle job processed event (job completed successfully)
     */
    public function handleJobProcessed(JobProcessed $event): void
    {
        Log::info('QueueJobEventListener: Job processed successfully', [
            'job_id' => $this->getJobId($event),
            'job_class' => $this->extractJobData($event)['class'],
            'queue' => $this->extractJobData($event)['queue'],
        ]);
        
        $this->recordJobCompletion($event, QueueJobStatistic::STATUS_SUCCESS);
    }

    /**
     * Handle job failed event
     */
    public function handleJobFailed(JobFailed $event): void
    {
        Log::info('QueueJobEventListener: Job failed', [
            'job_id' => $this->getJobId($event),
            'job_class' => $this->extractJobData($event)['class'],
            'queue' => $this->extractJobData($event)['queue'],
            'error' => $event->exception->getMessage(),
        ]);
        
        $this->recordJobCompletion($event, QueueJobStatistic::STATUS_FAILED, $event->exception);
    }

    /**
     * Record job completion statistics
     */
    private function recordJobCompletion($event, string $status, ?\Throwable $exception = null): void
    {
        try {
            $jobId = $this->getJobId($event);
            $startTime = $this->jobStartTimes[$jobId] ?? null;
            
            // Calculate execution time
            $executionTimeMs = null;
            if ($startTime) {
                $executionTimeMs = (int) ((microtime(true) - $startTime) * 1000);
                unset($this->jobStartTimes[$jobId]);
            }

            // Extract job information
            $jobData = $this->extractJobData($event);
            
            // Create statistics record
            $this->statisticRepository->create([
                'job_class' => $jobData['class'],
                'queue_name' => $jobData['queue'],
                'status' => $status,
                'execution_time_ms' => $executionTimeMs,
                'attempts' => $jobData['attempts'],
                'error_message' => $exception ? $exception->getMessage() : null,
                'connection' => $jobData['connection'],
                'started_at' => $startTime ? now()->subMilliseconds($executionTimeMs) : null,
                'completed_at' => now(),
            ]);

        } catch (\Exception $e) {
            // Log the error but don't let it break the job processing
            Log::error('Failed to record queue job statistics', [
                'error' => $e->getMessage(),
                'job_id' => $this->getJobId($event),
                'status' => $status,
            ]);
        }
    }

    /**
     * Extract job data from the event
     */
    private function extractJobData($event): array
    {
        $payload = $event->job->payload();
        $decodedPayload = json_decode($payload, true);
        
        return [
            'class' => $decodedPayload['displayName'] ?? $decodedPayload['job'] ?? 'Unknown',
            'queue' => $event->job->getQueue(),
            'attempts' => $event->job->attempts(),
            'connection' => $event->connectionName,
        ];
    }

    /**
     * Get unique job ID for tracking
     */
    private function getJobId($event): string
    {
        return $event->job->getJobId() ?? spl_object_hash($event->job);
    }
}
