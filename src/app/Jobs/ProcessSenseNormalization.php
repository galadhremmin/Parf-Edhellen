<?php

namespace App\Jobs;

use App\Models\Sense;
use App\Repositories\SenseTermRepository;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Rebuilds a sense's terms. The sense is re-read when the job runs, so a late job still normalises current data.
 */
class ProcessSenseNormalization implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public bool $deleteWhenMissingModels = true;

    public function __construct(protected Sense $sense) {}

    public function handle(SenseTermRepository $senseTermRepository): void
    {
        $senseTermRepository->rebuildSense($this->sense->id);
    }
}
