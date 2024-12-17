<?php

namespace App\Events;

use App\Models\Sentence;
use Illuminate\Queue\SerializesModels;

class SentenceDestroyed
{
    use SerializesModels;

    public $sentence;
    public $accountId;

    public function __construct(Sentence $sentence, int $accountId = 0)
    {
        $this->sentence = $sentence;
        $this->accountId = $accountId;
    }
}
