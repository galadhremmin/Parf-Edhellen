<?php

namespace App\Events;

use App\Models\Sentence;
use Illuminate\Queue\SerializesModels;

class SentenceEdited
{
    use SerializesModels;

    public function __construct(readonly Sentence $sentence, readonly int $accountId)
    {}
}
