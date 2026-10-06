<?php

namespace App\Events;

use App\Models\Sentence;
use Illuminate\Queue\SerializesModels;

class SentenceDestroyed
{
    use SerializesModels;

    public function __construct(readonly Sentence $sentence, readonly int $accountId = 0)
    {}
}
