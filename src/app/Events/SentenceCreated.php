<?php

namespace App\Events;

use App\Models\Sentence;
use Illuminate\Queue\SerializesModels;

class SentenceCreated
{
    use SerializesModels;

    public function __construct(readonly Sentence $sentence, readonly int $accountId)
    {}
}
