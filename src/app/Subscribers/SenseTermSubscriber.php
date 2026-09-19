<?php

namespace App\Subscribers;

use App\Events\SenseEdited;
use App\Jobs\ProcessSenseNormalization;

class SenseTermSubscriber
{
    public function subscribe(): array
    {
        return [
            SenseEdited::class => 'onSenseEdited',
        ];
    }

    public function onSenseEdited(SenseEdited $event): void
    {
        ProcessSenseNormalization::dispatch($event->sense)->onQueue('indexing');
    }
}
