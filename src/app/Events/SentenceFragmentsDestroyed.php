<?php

namespace App\Events;

use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class SentenceFragmentsDestroyed
{
    use SerializesModels;

    public Collection $sentence_fragments;

    public function __construct(Collection $sentence_fragments)
    {
        $this->sentence_fragments = $sentence_fragments;
    }
}
