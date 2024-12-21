<?php

namespace App\Events;

use App\Models\Sentence;
use Illuminate\Queue\SerializesModels;

class SentenceCreated
{
    use SerializesModels;

    public Sentence $sentence;

    public int $accountId;

    public function __construct(Sentence $sentence, int $accountId)
    {
        $this->sentence = $sentence;
        $this->accountId = $accountId;
    }
}
