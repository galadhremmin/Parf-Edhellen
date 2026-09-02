<?php

namespace App\Services\Flashcards;

/**
 * One (lexical entry, gloss) pair that a flashcard may be built from, either as the card itself or
 * as a distractor on somebody else's card.
 *
 * A plain immutable carrier so that the sampler can be unit tested without a database.
 */
class FlashcardCandidate
{
    public function __construct(
        public readonly int $lexicalEntryId,
        public readonly ?int $glossId,
        public readonly string $word,
        public readonly string $normalizedWord,
        public readonly string $translation,
        public readonly ?int $languageId = null,
        public readonly ?int $speechId = null,
        public readonly ?int $senseId = null,
        public readonly ?string $tengwar = null,
    ) {}

    /**
     * Builds a candidate from a row returned by LexicalEntryRepository::getRandomEntriesWithGlosses.
     */
    public static function fromRow(object $row): self
    {
        return new self(
            lexicalEntryId: (int) $row->lexical_entry_id,
            glossId: isset($row->gloss_id) ? (int) $row->gloss_id : null,
            word: (string) ($row->word ?? ''),
            normalizedWord: (string) ($row->normalized_word ?? ''),
            translation: (string) ($row->translation ?? ''),
            languageId: isset($row->language_id) ? (int) $row->language_id : null,
            speechId: isset($row->speech_id) ? (int) $row->speech_id : null,
            senseId: isset($row->sense_id) ? (int) $row->sense_id : null,
            tengwar: $row->tengwar ?? null,
        );
    }

    /**
     * The text shown for this candidate when asked in the given direction.
     */
    public function textFor(FlashcardDirection $direction): string
    {
        return $direction === FlashcardDirection::Forward
            ? $this->translation
            : $this->word;
    }
}
