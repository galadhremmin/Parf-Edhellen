<?php

namespace App\Services\Flashcards;

/**
 * The outcome of sampling distractors for a whole deck.
 */
class FlashcardDistractorSet
{
    /**
     * @param  array<int,FlashcardCandidate[]>  $distractors  keyed by the subject's lexical entry id
     * @param  int  $optionCount  the uniform number of options every dealt card carries
     * @param  int[]  $skipped  lexical entry ids that could not be given enough distractors
     */
    public function __construct(
        public readonly array $distractors,
        public readonly int $optionCount,
        public readonly array $skipped,
    ) {}
}
