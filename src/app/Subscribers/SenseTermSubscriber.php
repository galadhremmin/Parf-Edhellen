<?php

namespace App\Subscribers;

use App\Events\SenseEdited;
use App\Jobs\ProcessSenseConceptResolution;
use App\Jobs\ProcessSenseNormalization;
use Illuminate\Support\Facades\Bus;

class SenseTermSubscriber
{
    public function subscribe(): array
    {
        return [
            SenseEdited::class => 'onSenseEdited',
        ];
    }

    /**
     * The concept is resolved after the terms are rebuilt, in a chain: every step of the resolver reads them.
     */
    public function onSenseEdited(SenseEdited $event): void
    {
        Bus::chain([
            new ProcessSenseNormalization($event->sense),
            new ProcessSenseConceptResolution($event->sense),
        ])->onQueue('indexing')->dispatch();
    }
}
