<?php

namespace App\Interfaces;

use App\Services\Flashcards\FlashcardCandidate;

/**
 * Supplies the pool of words a flashcard deck draws its distractors from.
 *
 * An interface rather than a concrete dependency so that the sampler — which holds all of the
 * interesting rules — can be unit tested against a stub, with no database in sight.
 */
interface IFlashcardCandidateProvider
{
    /**
     * Draws a pool of candidates.
     *
     * @param  int[]  $languageIds  restricts the pool to these languages; empty means any
     * @param  int[]  $lexicalEntryGroupIds  restricts the pool to these groups; empty means any
     * @param  bool|null  $verbsOnly  true for verbs only, false to exclude verbs, null for either
     * @param  int[]  $excludeLexicalEntryIds  entries that must not appear in the pool
     * @return FlashcardCandidate[]
     */
    public function getPool(
        array $languageIds,
        array $lexicalEntryGroupIds,
        ?bool $verbsOnly,
        int $limit,
        array $excludeLexicalEntryIds = []
    ): array;
}
