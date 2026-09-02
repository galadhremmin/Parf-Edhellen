<?php

namespace App\Services\Flashcards;

/**
 * Decides whether an offered answer is right.
 *
 * The single authority on correctness, shared by the deck endpoint and the legacy per-card
 * endpoint. Keeping one definition matters: when the sampler and the scorer disagree about what
 * counts as the same answer, a distractor eventually gets accepted as correct.
 */
class FlashcardAnswerChecker
{
    private FlashcardAnswerNormalizer $_normalizer;

    public function __construct(FlashcardAnswerNormalizer $normalizer)
    {
        $this->_normalizer = $normalizer;
    }

    /**
     * @param  string[]  $acceptable  every answer that is right — forward, every gloss on the
     *                                entry; reverse, the entry's own word
     * @param  callable():string[]|null  $synonymFallback  consulted only when the strict comparison
     *                                                     fails, so the query it runs almost never
     *                                                     happens
     */
    public function isCorrect(string $offered, array $acceptable, ?callable $synonymFallback = null): bool
    {
        $normalizedOffer = $this->_normalizer->normalize($offered);

        // An empty answer means the card was abandoned, never that everything matches.
        if ($normalizedOffer === '') {
            return false;
        }

        if ($this->matchesAny($normalizedOffer, $acceptable)) {
            return true;
        }

        if ($synonymFallback === null) {
            return false;
        }

        // The sampler already excludes same-sense options, so this rarely fires. It exists because
        // those exclusions are heuristic, and marking a correct answer wrong is the most
        // trust-destroying thing a vocabulary trainer can do.
        return $this->matchesAny($normalizedOffer, $synonymFallback());
    }

    /**
     * @param  string[]  $acceptable
     */
    private function matchesAny(string $normalizedOffer, array $acceptable): bool
    {
        foreach ($acceptable as $candidate) {
            if ($this->_normalizer->matches($normalizedOffer, $candidate)) {
                return true;
            }
        }

        return false;
    }
}
