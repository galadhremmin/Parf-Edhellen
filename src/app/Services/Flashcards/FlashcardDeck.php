<?php

namespace App\Services\Flashcards;

/**
 * A finite, shuffled deck, dealt in one request and played once.
 */
class FlashcardDeck
{
    /**
     * @param  FlashcardDeckCard[]  $cards
     * @param  FlashcardSkippedCard[]  $skipped
     */
    public function __construct(
        public readonly string $name,
        public readonly FlashcardDirection $direction,
        public readonly int $optionCount,
        public readonly int $numberOfRequested,
        public readonly array $cards,
        public readonly array $skipped,
    ) {}
}
