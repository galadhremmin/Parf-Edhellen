<?php

namespace App\Services\Flashcards;

use App\Interfaces\IFlashcardCandidateProvider;

/**
 * Chooses the wrong answers offered beside the right one.
 *
 * Holds every rule about what makes a *plausible but unambiguously incorrect* option, and reaches
 * the database only through IFlashcardCandidateProvider, so the whole class unit tests against a
 * stub with no database at all.
 */
class FlashcardDistractorSampler
{
    /** Below this the exercise stops being a test of knowledge. */
    private const MINIMUM_OPTION_COUNT = 2;

    /**
     * How many pool rows to draw per distractor needed. Generous, because exclusion discards a lot:
     * synonyms, duplicates of the answer, and — in reverse — every repeat of the same Elvish word.
     */
    private const POOL_OVERSAMPLE = 8;

    private const MINIMUM_POOL_SIZE = 60;

    /** Beyond this, bucketing costs more queries than the plausibility is worth. */
    private const MAXIMUM_BUCKETS = 4;

    private IFlashcardCandidateProvider $_provider;

    private FlashcardAnswerNormalizer $_normalizer;

    private VerbSpeechCatalogue $_verbs;

    public function __construct(
        IFlashcardCandidateProvider $provider,
        FlashcardAnswerNormalizer $normalizer,
        VerbSpeechCatalogue $verbs
    ) {
        $this->_provider = $provider;
        $this->_normalizer = $normalizer;
        $this->_verbs = $verbs;
    }

    /**
     * @param  FlashcardCandidate[]  $subjects  one per card, already deduplicated by entry
     * @param  array<int,string[]>  $translationsByEntryId  every gloss on each subject entry
     * @param  int[]  $languageIds  languages the deck draws from
     * @param  int[]  $lexicalEntryGroupIds  groups the deck draws from
     */
    public function sample(
        array $subjects,
        FlashcardDirection $direction,
        array $translationsByEntryId,
        array $languageIds,
        array $lexicalEntryGroupIds,
        int $optionCount = 4
    ): FlashcardDistractorSet {
        if (empty($subjects)) {
            return new FlashcardDistractorSet([], $optionCount, []);
        }

        $subjectIds = array_map(fn (FlashcardCandidate $s) => $s->lexicalEntryId, $subjects);
        $buckets = $this->bucket($subjects, $direction);

        // One pool query per bucket, not per card: the query count is what makes a deck viable.
        $pools = [];
        foreach ($buckets as $key => $bucketSubjects) {
            $pools[$key] = $this->drawPool(
                $bucketSubjects, $direction, $key, $languageIds, $lexicalEntryGroupIds, $optionCount, $subjectIds
            );
        }

        // Drawn lazily: only decks that fall short of their option count ever pay for it.
        $fallbackPool = null;

        $chosen = [];
        foreach ($buckets as $key => $bucketSubjects) {
            foreach ($bucketSubjects as $subject) {
                $options = $this->chooseFor(
                    $subject, $direction, $translationsByEntryId, $pools[$key], $optionCount - 1
                );

                if (count($options) < $optionCount - 1) {
                    if ($fallbackPool === null) {
                        $fallbackPool = $this->drawPool(
                            $subjects, $direction, null, [], $lexicalEntryGroupIds, $optionCount, $subjectIds
                        );
                    }

                    $options = $this->chooseFor(
                        $subject, $direction, $translationsByEntryId, $fallbackPool, $optionCount - 1, $options
                    );
                }

                $chosen[$subject->lexicalEntryId] = $options;
            }
        }

        return $this->levelOff($chosen, $optionCount);
    }

    /**
     * Reduces the deck to a single option count.
     *
     * A card offering three options where its neighbours offer four tells the player something
     * about itself before they have answered, so the count is uniform or the deck is wrong.
     *
     * @param  array<int,FlashcardCandidate[]>  $chosen
     */
    private function levelOff(array $chosen, int $optionCount): FlashcardDistractorSet
    {
        $achievable = $optionCount - 1;
        foreach ($chosen as $options) {
            $achievable = min($achievable, count($options));
        }

        // Cards that cannot reach even the floor are reported, never quietly dealt short.
        $floor = self::MINIMUM_OPTION_COUNT - 1;
        $achievable = max($achievable, $floor);

        $distractors = [];
        $skipped = [];
        foreach ($chosen as $lexicalEntryId => $options) {
            if (count($options) < $floor) {
                $skipped[] = $lexicalEntryId;

                continue;
            }

            $distractors[$lexicalEntryId] = array_slice($options, 0, $achievable);
        }

        return new FlashcardDistractorSet($distractors, $achievable + 1, $skipped);
    }

    /**
     * Groups subjects so that each group can be served by one pool query.
     *
     * Forward buckets on verbs alone. Reverse also buckets on language, because offering a Quenya
     * word among Sindarin ones gives the answer away on sight — but caps the number of buckets, and
     * therefore of queries, by merging the least populated languages.
     *
     * @param  FlashcardCandidate[]  $subjects
     * @return array<string,FlashcardCandidate[]>
     */
    private function bucket(array $subjects, FlashcardDirection $direction): array
    {
        $buckets = [];
        foreach ($subjects as $subject) {
            $isVerb = $this->_verbs->isVerb($subject->speechId) ? 'v' : 'n';
            $key = $direction === FlashcardDirection::Reverse
                ? $subject->languageId.':'.$isVerb
                : $isVerb;

            $buckets[$key][] = $subject;
        }

        if (count($buckets) <= self::MAXIMUM_BUCKETS) {
            return $buckets;
        }

        uasort($buckets, fn ($a, $b) => count($b) <=> count($a));

        $kept = array_slice($buckets, 0, self::MAXIMUM_BUCKETS - 1, true);
        $merged = [];
        foreach (array_slice($buckets, self::MAXIMUM_BUCKETS - 1, null, true) as $bucket) {
            $merged = array_merge($merged, $bucket);
        }

        $kept['merged'] = $merged;

        return $kept;
    }

    /**
     * Draws the pool for one bucket. A null $key merges every language, which is the fallback used
     * when a narrow bucket cannot fill a card.
     *
     * @param  FlashcardCandidate[]  $bucketSubjects
     * @param  int[]  $languageIds
     * @param  int[]  $lexicalEntryGroupIds
     * @param  int[]  $subjectIds
     * @return FlashcardCandidate[]
     */
    private function drawPool(
        array $bucketSubjects,
        FlashcardDirection $direction,
        ?string $key,
        array $languageIds,
        array $lexicalEntryGroupIds,
        int $optionCount,
        array $subjectIds
    ): array {
        $verbsOnly = null;
        $poolLanguageIds = $languageIds;

        if ($key !== null && $key !== 'merged') {
            $parts = explode(':', $key);
            $verbFlag = end($parts);
            $verbsOnly = $verbFlag === 'v';

            if ($direction === FlashcardDirection::Reverse && count($parts) === 2 && $parts[0] !== '') {
                $poolLanguageIds = [(int) $parts[0]];
            }
        }

        $needed = count($bucketSubjects) * max(1, $optionCount - 1);
        $limit = max(self::MINIMUM_POOL_SIZE, $needed * self::POOL_OVERSAMPLE);

        $pool = $this->_provider->getPool(
            $poolLanguageIds, $lexicalEntryGroupIds, $verbsOnly, $limit, $subjectIds
        );

        return $direction === FlashcardDirection::Reverse
            ? $this->collapseToDistinctWords($pool)
            : $pool;
    }

    /**
     * In reverse the option text is the Elvish word, and the same word occurs across many entries —
     * an uncollapsed pool of 400 rows can hold only a few dozen distinct words, and the same word
     * would otherwise be offered twice on one card.
     *
     * @param  FlashcardCandidate[]  $pool
     * @return FlashcardCandidate[]
     */
    private function collapseToDistinctWords(array $pool): array
    {
        $byWord = [];
        foreach ($pool as $candidate) {
            $key = $candidate->normalizedWord !== ''
                ? $candidate->normalizedWord
                : $this->_normalizer->normalize($candidate->word);

            if (! isset($byWord[$key])) {
                $byWord[$key] = $candidate;
            }
        }

        return array_values($byWord);
    }

    /**
     * Picks distractors for one card.
     *
     * @param  array<int,string[]>  $translationsByEntryId
     * @param  FlashcardCandidate[]  $pool
     * @param  FlashcardCandidate[]  $alreadyChosen
     * @return FlashcardCandidate[]
     */
    private function chooseFor(
        FlashcardCandidate $subject,
        FlashcardDirection $direction,
        array $translationsByEntryId,
        array $pool,
        int $needed,
        array $alreadyChosen = []
    ): array {
        $chosen = $alreadyChosen;

        // Everything the card already shows, so that no option is offered twice.
        $taken = [];
        foreach ($chosen as $candidate) {
            $taken[$this->_normalizer->normalize($candidate->textFor($direction))] = true;
        }

        $forbidden = $this->forbiddenTexts($subject, $direction, $translationsByEntryId);

        shuffle($pool);

        foreach ($pool as $candidate) {
            if (count($chosen) >= $needed) {
                break;
            }

            if ($candidate->lexicalEntryId === $subject->lexicalEntryId) {
                continue;
            }

            // Sharing a sense is the cheapest high-yield synonym filter there is, and it catches
            // pairs that no amount of string comparison would.
            if ($subject->senseId !== null && $candidate->senseId === $subject->senseId) {
                continue;
            }

            $text = $candidate->textFor($direction);
            if ($text === '') {
                continue;
            }

            $normalized = $this->_normalizer->normalize($text);
            if ($normalized === '' || isset($taken[$normalized]) || isset($forbidden[$normalized])) {
                continue;
            }

            // In reverse, a different word that also means the prompt is a genuinely correct
            // answer. Offering it as a distractor would mark a right answer wrong.
            if ($direction === FlashcardDirection::Reverse
                && $this->alsoMeans($candidate, $subject->translation, $translationsByEntryId)) {
                continue;
            }

            $taken[$normalized] = true;
            $chosen[] = $candidate;
        }

        return $chosen;
    }

    /**
     * Text that must never be offered as a wrong answer for this card, because it is right.
     *
     * @param  array<int,string[]>  $translationsByEntryId
     * @return array<string,true>
     */
    private function forbiddenTexts(
        FlashcardCandidate $subject,
        FlashcardDirection $direction,
        array $translationsByEntryId
    ): array {
        $forbidden = [];

        if ($direction === FlashcardDirection::Forward) {
            // Every gloss on the entry, not merely the one shown: the others are equally correct.
            foreach ($translationsByEntryId[$subject->lexicalEntryId] ?? [$subject->translation] as $translation) {
                $forbidden[$this->_normalizer->normalize($translation)] = true;
            }
        } else {
            $forbidden[$this->_normalizer->normalize($subject->word)] = true;
        }

        unset($forbidden['']);

        return $forbidden;
    }

    /**
     * Whether the candidate word also translates to the prompt.
     *
     * @param  array<int,string[]>  $translationsByEntryId
     */
    private function alsoMeans(
        FlashcardCandidate $candidate,
        string $prompt,
        array $translationsByEntryId
    ): bool {
        $normalizedPrompt = $this->_normalizer->normalize($prompt);
        if ($normalizedPrompt === '') {
            return false;
        }

        $translations = $translationsByEntryId[$candidate->lexicalEntryId] ?? [$candidate->translation];
        foreach ($translations as $translation) {
            if ($this->_normalizer->normalize($translation) === $normalizedPrompt) {
                return true;
            }
        }

        return false;
    }
}
