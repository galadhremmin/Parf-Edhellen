<?php

namespace App\Services\Flashcards;

use App\Models\LexicalEntry;

/**
 * One card in a dealt deck, still in domain form: the adapter turns it into JSON.
 */
class FlashcardDeckCard
{
    /**
     * @param  FlashcardCandidate[]  $distractors
     */
    public function __construct(
        public readonly LexicalEntry $entry,
        public readonly ?int $glossId,
        public readonly string $translation,
        public readonly array $distractors,
    ) {}
}
