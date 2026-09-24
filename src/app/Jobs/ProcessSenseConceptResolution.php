<?php

namespace App\Jobs;

use App\Models\Sense;
use App\Repositories\SenseTermRepository;
use App\Services\Senses\SenseConceptResolver;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Works out what a sense means, once its terms are in place. Runs after ProcessSenseNormalization, which the
 * resolver's steps read.
 */
class ProcessSenseConceptResolution implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public bool $deleteWhenMissingModels = true;

    public function __construct(protected Sense $sense) {}

    public function handle(SenseTermRepository $senseTerms, SenseConceptResolver $resolver): void
    {
        $sense = $senseTerms->normalizable()->with('terms')->whereKey($this->sense->id)->first();

        // no active entry uses the sense any more
        if ($sense !== null) {
            $resolver->resolve($sense);
        }
    }
}
