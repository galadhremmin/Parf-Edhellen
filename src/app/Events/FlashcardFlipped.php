<?php

namespace App\Events;

use App\Models\FlashcardResult;
use Illuminate\Queue\SerializesModels;

class FlashcardFlipped
{
    use SerializesModels;

    public FlashcardResult $result;

    public int $numberOfCards;

    public function __construct(FlashcardResult $result, int $numberOfCards)
    {
        $this->result = $result;
        $this->numberOfCards = $numberOfCards;
    }
}
