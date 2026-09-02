<?php

namespace App\Services\Flashcards;

use App\Interfaces\IFlashcardDeckSource;
use App\Models\Gloss;
use App\Models\LexicalEntry;

/**
 * Deals a finite deck from any source of words.
 *
 * Named for the deck rather than for the word list because it is genuinely source agnostic — see
 * IFlashcardDeckSource. That is what makes "built once, used by both directions and both sources"
 * true rather than aspirational.
 */
class FlashcardDeckBuilder
{
    public const DEFAULT_SIZE = 20;

    public const MAXIMUM_SIZE = 50;

    private FlashcardDistractorSampler $_sampler;

    private FlashcardAnswerNormalizer $_normalizer;

    public function __construct(FlashcardDistractorSampler $sampler, FlashcardAnswerNormalizer $normalizer)
    {
        $this->_sampler = $sampler;
        $this->_normalizer = $normalizer;
    }

    /**
     * @param  int[]|null  $lexicalEntryIds  restrict the deck to this subset, for retrying misses
     */
    public function build(
        IFlashcardDeckSource $source,
        FlashcardDirection $direction,
        int $size = self::DEFAULT_SIZE,
        ?array $lexicalEntryIds = null,
        int $optionCount = 4
    ): FlashcardDeck {
        $size = max(1, min($size, self::MAXIMUM_SIZE));
        $entries = $source->getCandidateEntries($lexicalEntryIds, $size);

        $subjects = [];
        $translationsByEntryId = [];
        $entriesById = [];
        $glossesById = [];
        $skipped = [];

        foreach ($entries as $entry) {
            $gloss = $this->pickGloss($entry);

            if ($gloss === null) {
                // Reported, not recursed around. The old per-card endpoint retried up to ten times
                // and still gave no account of what it had thrown away.
                $skipped[] = new FlashcardSkippedCard(
                    $entry->id,
                    (string) ($entry->word?->word ?? ''),
                    FlashcardSkippedCard::REASON_NO_TRANSLATION
                );

                continue;
            }

            $entriesById[$entry->id] = $entry;
            $glossesById[$entry->id] = $gloss;
            $translationsByEntryId[$entry->id] = $entry->glosses
                ->pluck('translation')
                ->filter()
                ->values()
                ->all();

            $subjects[] = new FlashcardCandidate(
                lexicalEntryId: $entry->id,
                glossId: $gloss->id,
                word: (string) ($entry->word?->word ?? ''),
                normalizedWord: (string) ($entry->word?->normalized_word ?? ''),
                translation: (string) $gloss->translation,
                languageId: $entry->language_id,
                speechId: $entry->speech_id,
                senseId: $entry->sense_id,
                tengwar: $entry->tengwar,
            );
        }

        $set = $this->_sampler->sample(
            $subjects,
            $direction,
            $translationsByEntryId,
            $source->getLanguageIds(),
            $source->getLexicalEntryGroupIds(),
            $optionCount
        );

        $cards = [];
        foreach ($subjects as $subject) {
            $id = $subject->lexicalEntryId;

            if (! isset($set->distractors[$id])) {
                $skipped[] = new FlashcardSkippedCard(
                    $id, $subject->word, FlashcardSkippedCard::REASON_NO_DISTRACTORS
                );

                continue;
            }

            $cards[] = new FlashcardDeckCard(
                $entriesById[$id], $glossesById[$id]->id, $subject->translation, $set->distractors[$id]
            );
        }

        shuffle($cards);

        return new FlashcardDeck(
            name: $source->getName(),
            direction: $direction,
            optionCount: $set->optionCount,
            numberOfRequested: $entries->count(),
            cards: $cards,
            skipped: $skipped,
        );
    }

    /**
     * Chooses the gloss a card is asked on, or null when the entry has nothing usable.
     *
     * A gloss that merely repeats the word teaches nothing, and the placeholder glosses ("?",
     * "[unglossed]") are not translations at all.
     */
    private function pickGloss(LexicalEntry $entry): ?Gloss
    {
        $excluded = array_map(
            fn ($value) => $this->_normalizer->normalize($value),
            config('ed.flashcard_excluded_translations', [])
        );

        $normalizedWord = $this->_normalizer->normalize($entry->word?->word);

        $usable = $entry->glosses->filter(function (Gloss $gloss) use ($normalizedWord, $excluded) {
            $normalized = $this->_normalizer->normalize($gloss->translation);

            return $normalized !== ''
                && $normalized !== $normalizedWord
                && ! in_array($normalized, $excluded, true);
        });

        return $usable->count() > 0 ? $usable->random() : null;
    }
}
