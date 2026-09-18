<?php

namespace App\Services\Senses;

use Illuminate\Support\Collection;
use Normalizer;

/**
 * Splits a sense into its terms and reduces each to a key, so that "trees", "tree (tall)", "pine-tree" and
 * "pine tree" group the way a reader expects. Senses are gloss lists ("gate, door"), so the first term is the
 * headword that groups them; the rest are its elaborations.
 *
 * The rules are deterministic and cheap: the same sense always yields the same keys, locally and in production.
 */
class SenseNormalizer
{
    // a literal translation or an editorial note is not a synonym
    private const ANNOTATION = '/^\s*[\(\[](lit|orig|cf|pl|sg|fem|masc)\.?[\)\]]/u';

    private const QUALIFIER = '/[\(\[][^\)\]]*[\)\]]/u';

    // * reconstructed, ? uncertain, and quotes around coined names
    private const EDITORIAL_MARKS = '/[*?!"\'.:]+/u';

    // WordNet has lemmas for short function words too (was → wa, is → i), so they are never lemmatised
    private const MIN_LEMMATIZED_LENGTH = 4;

    private const MAX_LENGTH = 250;

    public function __construct(private readonly NounLemmatizer $_lemmatizer) {}

    /**
     * @param  bool  $isVerb  whether every entry with this sense is a verb, for senses written without "to"
     * @return Collection<int, NormalizedTerm>
     */
    public function normalize(string $sense, bool $isVerb = false): Collection
    {
        $terms = collect();
        foreach ($this->split($sense) as $part) {
            if (preg_match(self::ANNOTATION, $part)) {
                continue;
            }

            $term = $this->term($part, $isVerb, $terms->count());
            if ($term !== null) {
                $terms->push($term);
            }
        }

        return $terms;
    }

    /**
     * Splits on commas and semicolons outside brackets: "wood, forest (of trees; rare)" has two terms.
     *
     * @return Collection<int, string>
     */
    private function split(string $sense): Collection
    {
        $sense = Normalizer::normalize($sense, Normalizer::FORM_KC);
        $sense = mb_strtolower(strtr($sense, ['’' => "'", '‘' => "'", '“' => '"', '”' => '"', '‽' => '?']));

        $parts = collect();
        $depth = 0;
        $buffer = '';
        foreach (mb_str_split($sense) as $character) {
            $depth += match ($character) {
                '(', '[' => 1,
                ')', ']' => $depth > 0 ? -1 : 0,
                default => 0,
            };

            if ($depth === 0 && ($character === ',' || $character === ';')) {
                $parts->push($buffer);
                $buffer = '';
            } else {
                $buffer .= $character;
            }
        }

        return $parts->push($buffer);
    }

    /**
     * @return NormalizedTerm|null null for a term with nothing left to compare
     */
    private function term(string $part, bool $isVerb, int $position): ?NormalizedTerm
    {
        $term = preg_replace('/^\s*[\(\[]to[\)\]]\s*/u', 'to ', $part);
        $term = preg_replace(self::QUALIFIER, ' ', $term);
        $term = preg_replace(self::EDITORIAL_MARKS, ' ', $term);
        $term = preg_replace('/\s+/u', ' ', trim($term));
        $term = preg_replace('/^(a|an|the) /u', '', $term);

        if (preg_match('/^to (.+)$/u', $term, $matches)) {
            $term = $matches[1];
            $isVerb = true;
        }

        if (! $isVerb) {
            $term = preg_replace_callback('/\p{L}+$/u', fn (array $word) => mb_strlen($word[0]) >= self::MIN_LEMMATIZED_LENGTH
                ? $this->_lemmatizer->lemmatize($word[0])
                : $word[0], $term);
        }

        // pine tree, pine-tree and pinetree are one key; diacritics stay, so éyë isn't eye
        $key = preg_replace('/[\s\-]+/u', '', $term);
        if ($key === '') {
            return null;
        }

        return new NormalizedTerm(
            $position,
            mb_substr(trim($part), 0, self::MAX_LENGTH),
            mb_substr(($isVerb ? 'to:' : '').$key, 0, self::MAX_LENGTH),
            $isVerb,
        );
    }
}
