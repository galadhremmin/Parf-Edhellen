<?php

namespace App\Interfaces;

use Illuminate\Support\Collection;

/**
 * Where a deck of flashcards gets its words from.
 *
 * A word list today; a language flashcard tomorrow. The builder is written against this so that
 * adding the second source costs one small class rather than a second deck implementation.
 */
interface IFlashcardDeckSource
{
    public function getName(): string;

    /**
     * Languages represented in the source, used to keep distractors plausible.
     *
     * @return int[]
     */
    public function getLanguageIds(): array;

    /**
     * @return int[]
     */
    public function getLexicalEntryGroupIds(): array;

    /**
     * The entries a deck may be dealt from, eager loaded with word, language, speech and glosses.
     *
     * @param  int[]|null  $lexicalEntryIds  restricts to this subset — used to retry missed words.
     *                                       Implementations MUST intersect it with what the source
     *                                       actually holds rather than trusting it.
     * @return Collection<int,\App\Models\LexicalEntry>
     */
    public function getCandidateEntries(?array $lexicalEntryIds, int $limit): Collection;
}
