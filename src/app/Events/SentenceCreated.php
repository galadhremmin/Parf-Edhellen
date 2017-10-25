<?php

namespace App\Events;

use App\Models\Sentence;
use Illuminate\Queue\SerializesModels;

class SentenceCreated
{
    use SerializesModels;

    public $sentence;
    public $accountId;

    public function __construct(Sentence $sentence, int $accountId)
    {
        $this->sentence = $sentence;
        $this->accountId = $accountId;
    }
}
