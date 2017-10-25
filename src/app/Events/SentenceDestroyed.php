<?php

namespace App\Events;

use App\Models\Sentence;
use Illuminate\Queue\SerializesModels;

class SentenceDestroyed
{
    use SerializesModels;

    public $sentence;
    public function __construct(Sentence $sentence)
    {
        $this->sentence = $sentence;
    }
}
