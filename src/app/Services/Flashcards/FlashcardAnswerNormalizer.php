<?php

namespace App\Services\Flashcards;

use App\Helpers\StringHelper;

/**
 * Normalises flashcard answers so that they can be compared with one another.
 *
 * This deliberately lives with the flashcard game rather than in StringHelper: the rules below are
 * specific to how a vocabulary answer is judged (an infinitive marker is noise, a parenthetical
 * qualifier is noise), and would be surprising as general purpose string handling.
 *
 * Both the distractor sampler and the answer checker depend on this class, so that a translation
 * which is rejected as a distractor is guaranteed to be accepted as an answer, and vice versa. If
 * the two ever disagreed, the game would offer an option that it then marks wrong.
 */
class FlashcardAnswerNormalizer
{
    /**
     * Marker prefixes stripped from a translation before comparison. English glosses for verbs are
     * inconsistently recorded with and without the infinitive marker.
     */
    private const INFINITIVE_PREFIXES = ['to '];

    /**
     * Reduces the given value to its comparable form. Returns an empty string for a value that
     * carries no comparable content at all.
     */
    public function normalize(?string $value): string
    {
        if ($value === null) {
            return '';
        }

        $comparable = trim($value);

        // Trailing qualifiers such as "star (of the sky)" narrow the sense rather than change it.
        // Applied before transliteration, which would otherwise leave the brackets in place.
        $comparable = preg_replace('/\s*\([^)]*\)\s*$/u', '', $comparable);

        // Collapse internal runs of whitespace so that "a  star" and "a star" agree.
        $comparable = preg_replace('/\s+/u', ' ', trim($comparable));

        if ($comparable === '') {
            return '';
        }

        // transliterate(), not normalize(): the latter slugifies (spaces become underscores,
        // brackets become hyphens), which is right for keyword lookup and wrong for comparing
        // human readable answers. Accents are folded either way.
        $comparable = StringHelper::transliterate($comparable, false);

        foreach (self::INFINITIVE_PREFIXES as $prefix) {
            if (str_starts_with($comparable, $prefix)) {
                $comparable = substr($comparable, strlen($prefix));
                break;
            }
        }

        return trim($comparable);
    }

    /**
     * Whether two answers are equivalent for the purposes of the game.
     */
    public function matches(?string $a, ?string $b): bool
    {
        $normalizedA = $this->normalize($a);

        return $normalizedA !== '' && $normalizedA === $this->normalize($b);
    }
}
