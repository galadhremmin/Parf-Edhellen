<?php

namespace App\Events;

use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class SentenceFragmentsDestroyed
{
    use SerializesModels;

    public function __construct(readonly Collection $sentence_fragments)
    {}
}
