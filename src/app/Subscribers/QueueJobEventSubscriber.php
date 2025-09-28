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
        $jobId = $this->getJobId($event);
        $jobData = $this->extractJobData($event);
        
        Log::info('QueueJobEventListener: Job processing started', [
            'job_id' => $jobId,
            'job_class' => $jobData['class'],
            'queue' => $jobData['queue'],
        ]);
        
        try {
            // Create initial record for active job
            $this->statisticRepository->create([
                'job_class' => $jobData['class'],
                'queue_name' => $jobData['queue'],
                'status' => 'processing',
                'attempts' => $jobData['attempts'],
                'connection' => $jobData['connection'],
                'job_id' => $jobId,
                'started_at' => now(),
                'is_active' => true,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to record job start time', [
                'error' => $e->getMessage(),
                'job_id' => $jobId,
                'job_class' => $jobData['class'],
            ]);
        }
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
            $jobData = $this->extractJobData($event);
            $completedAt = now();
            
            // Find the active job record by job ID (preferred method)
            $activeJob = $this->statisticRepository->findActiveJobById($jobId);
            
            if ($activeJob) {
                // Calculate execution time
                $executionTimeMs = null;
                if ($activeJob->started_at) {
                    $executionTimeMs = (int) $activeJob->started_at->diffInMilliseconds($completedAt);
                }
                
                // Update the existing record
                $this->statisticRepository->updateJobCompletion($activeJob, [
                    'status' => $status,
                    'execution_time_ms' => $executionTimeMs,
                    'error_message' => $exception ? $exception->getMessage() : null,
                    'completed_at' => $completedAt,
                    'is_active' => false,
                ]);
            } else {
                // Fallback: create a new record if we can't find the active one
                Log::warning('Could not find active job record, creating new record', [
                    'job_id' => $jobId,
                    'job_class' => $jobData['class'],
                    'status' => $status,
                ]);
                
                $this->statisticRepository->create([
                    'job_class' => $jobData['class'],
                    'queue_name' => $jobData['queue'],
                    'status' => $status,
                    'execution_time_ms' => null,
                    'attempts' => $jobData['attempts'],
                    'error_message' => $exception ? $exception->getMessage() : null,
                    'connection' => $jobData['connection'],
                    'job_id' => $jobId,
                    'started_at' => null,
                    'completed_at' => $completedAt,
                    'is_active' => false,
                ]);
            }

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
        
        // Handle both string and array payloads
        if (is_array($payload)) {
            $decodedPayload = $payload;
        } else {
            $decodedPayload = json_decode($payload, true);
        }
        
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
