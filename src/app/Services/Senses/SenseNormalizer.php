<?php

namespace App\Services\Senses;

use App\Helpers\StringHelper;
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
    // a term opening with a note such as "(lit.)" or "[orig.]": a literal translation or an editorial note is not a synonym
    private const ANNOTATION = '/^\s*[\(\[](lit|orig|cf|pl|sg|fem|masc)\.?[\)\]]/u';

    // anything in round or square brackets: "(tall) tree", "[dark] stain"
    private const QUALIFIER = '/[\(\[][^\)\]]*[\)\]]/u';

    // * reconstructed, ? uncertain, and quotes around coined names
    private const EDITORIAL_MARKS = '/[*?!"\'.:]+/u';

    // WordNet has lemmas for short function words too (was → wa, is → i), so they are never lemmatised
    private const MIN_LEMMATIZED_LENGTH = 4;

    private const MAX_LENGTH = 250;

    public function __construct(private readonly NounLemmatizer $_lemmatizer) {}

    /**
     * Splits a sense into its terms, skips annotations such as "(lit.) shining one", and keys the rest.
     *
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
        // removes the markers a lexicographer hangs on a word: † archaic, # reconstructed, √ root
        $sense = StringHelper::clean($sense);

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
     * Strips a term down to what identifies it (qualifiers, editorial marks, articles, "to"), lemmatises its last
     * word and compacts it into a key.
     *
     * @return NormalizedTerm|null null for a term with nothing left to compare
     */
    private function term(string $part, bool $isVerb, int $position): ?NormalizedTerm
    {
        // "[to] free" → "to free", before the brackets are dropped with the other qualifiers
        $term = preg_replace('/^\s*[\(\[]to[\)\]]\s*/u', 'to ', $part);
        $term = preg_replace(self::QUALIFIER, ' ', $term);
        $term = preg_replace(self::EDITORIAL_MARKS, ' ', $term);
        // an unclosed bracket survives the qualifier pattern: "(great" is the headword "great"
        $term = preg_replace('/[\(\)\[\]]/u', ' ', $term);
        // collapse the gaps the removals leave
        $term = preg_replace('/\s+/u', ' ', trim($term));
        // "the Elves" → "elves"
        $term = preg_replace('/^(a|an|the) /u', '', $term);

        // "to fall" → "fall", remembered as a verb
        if (preg_match('/^to (.+)$/u', $term, $matches)) {
            $term = $matches[1];
            $isVerb = true;
        }

        // only the last word of a noun phrase carries the plural: "oak-trees" → "oak-tree"
        $reducedFrom = null;
        $lastWord = $isVerb ? null : $this->lastWord($term);
        if ($lastWord !== null) {
            $lemma = $this->_lemmatizer->lemmatize($lastWord);
            if ($lemma !== $lastWord) {
                $reducedFrom = $lastWord;
                $term = mb_substr($term, 0, -mb_strlen($lastWord)).$lemma;
            }
        }

        // drop spaces and hyphens: pine tree, pine-tree and pinetree are one key; diacritics stay, so éyë isn't eye
        $key = preg_replace('/[\s\-]+/u', '', $term);
        if ($key === '') {
            return null;
        }

        return new NormalizedTerm(
            $position,
            mb_substr(trim($part), 0, self::MAX_LENGTH),
            mb_substr(($isVerb ? 'to:' : '').$key, 0, self::MAX_LENGTH),
            mb_substr($term, 0, self::MAX_LENGTH),
            $isVerb,
            $reducedFrom,
        );
    }

    /**
     * The word a term ends with, or null when it is too short to lemmatise safely.
     */
    private function lastWord(string $term): ?string
    {
        // the run of letters at the end: "trees" in "oak-trees"
        if (! preg_match('/\p{L}+$/u', $term, $matches) || mb_strlen($matches[0]) < self::MIN_LEMMATIZED_LENGTH) {
            return null;
        }

        return $matches[0];
    }
}
