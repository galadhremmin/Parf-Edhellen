<?php

namespace App\Services\Flashcards;

/**
 * A word that could not be turned into a card, and why.
 *
 * Reported rather than silently dropped: somebody who curated exactly twenty words deserves to be
 * told that only seventeen were dealt.
 */
class FlashcardSkippedCard
{
    public const REASON_NO_TRANSLATION = 'no-translation';

    public const REASON_NO_DISTRACTORS = 'no-distractors';

    public function __construct(
        public readonly int $lexicalEntryId,
        public readonly string $word,
        public readonly string $reason,
    ) {}
}
