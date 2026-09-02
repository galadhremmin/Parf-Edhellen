<?php

namespace App\Services\Flashcards;

use App\Interfaces\IFlashcardCandidateProvider;
use App\Repositories\LexicalEntryRepository;
use App\Repositories\ValueObjects\LexicalEntrySamplingValue;

/**
 * Draws flashcard candidates out of the dictionary.
 *
 * This is the seam between game rules and data access: it turns "verbs only" and "no placeholder
 * glosses" into the plain criteria the repository understands, so that the repository — which is
 * used all over the application — never learns what a flashcard is.
 */
class LexicalEntryCandidateProvider implements IFlashcardCandidateProvider
{
    private LexicalEntryRepository $_repository;

    private VerbSpeechCatalogue $_verbs;

    public function __construct(LexicalEntryRepository $repository, VerbSpeechCatalogue $verbs)
    {
        $this->_repository = $repository;
        $this->_verbs = $verbs;
    }

    public function getPool(
        array $languageIds,
        array $lexicalEntryGroupIds,
        ?bool $verbsOnly,
        int $limit,
        array $excludeLexicalEntryIds = []
    ): array {
        $criteria = [
            'language_ids' => array_values($languageIds),
            'lexical_entry_group_ids' => array_values($lexicalEntryGroupIds),
            'exclude_lexical_entry_ids' => array_values($excludeLexicalEntryIds),
            // Placeholder glosses would otherwise turn up as options reading "?" or "[unglossed]".
            'exclude_translations' => config('ed.flashcard_excluded_translations', []),
            'limit' => $limit,
        ];

        // English renders most verbs in the infinitive, so a verb sitting among nouns is obvious
        // from its shape alone. Bucketing keeps the options plausible.
        if ($verbsOnly === true) {
            $criteria['speech_ids'] = $this->_verbs->getIds();
        } elseif ($verbsOnly === false) {
            $criteria['exclude_speech_ids'] = $this->_verbs->getIds();
        }

        $rows = $this->_repository->getRandomEntriesWithGlosses(
            new LexicalEntrySamplingValue($criteria)
        );

        return $rows->map(fn ($row) => FlashcardCandidate::fromRow($row))->all();
    }
}
