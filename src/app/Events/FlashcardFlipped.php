<?php

namespace App\Events;

use App\Models\FlashcardResult;
use Illuminate\Queue\SerializesModels;

class FlashcardFlipped
{
    use SerializesModels;

    public function __construct(readonly FlashcardResult $result, readonly int $numberOfCards)
    {}
}
