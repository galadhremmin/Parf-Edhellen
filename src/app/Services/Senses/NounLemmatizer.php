<?php

namespace App\Services\Senses;

use App\Interfaces\IWordNetLexicon;
use App\Services\Enumerations\WordNetPos;

/**
 * Reduces a plural noun to its singular with WordNet's morphy rules. A base form is only accepted when WordNet
 * knows it, so words WordNet doesn't know (most of Tolkien's) are left alone.
 */
class NounLemmatizer
{
    // morphy's detachment rules for nouns, in the order WordNet applies them
    private const RULES = [
        ['s', ''], ['ses', 's'], ['xes', 'x'], ['zes', 'z'], ['ches', 'ch'], ['shes', 'sh'], ['men', 'man'], ['ies', 'y'],
    ];

    public function __construct(private readonly IWordNetLexicon $_lexicon) {}

    /**
     * The singular of a plural noun; any other word comes back unchanged.
     */
    public function lemmatize(string $word): string
    {
        $irregular = $this->_lexicon->exceptionBases($word, WordNetPos::NOUN);
        if ($irregular) {
            return $irregular[0];
        }

        $base = $this->regularBase($word);
        if ($base === null) {
            return $word;
        }

        // A word that is a lemma in its own right stays, unless its base is at least as common: pass, species and
        // physics stay; days, eyes and men reduce.
        if ($this->_lexicon->isLemma($word) && $this->_lexicon->tagCount($word) >= $this->_lexicon->tagCount($base)) {
            return $word;
        }

        return $base;
    }

    /**
     * Applies the suffix rules in order; the first result WordNet knows as a noun wins.
     */
    private function regularBase(string $word): ?string
    {
        foreach (self::RULES as [$suffix, $ending]) {
            if (str_ends_with($word, $suffix)) {
                $base = substr($word, 0, -strlen($suffix)).$ending;
                if ($base !== $word && $base !== '' && $this->_lexicon->isLemma($base, WordNetPos::NOUN)) {
                    return $base;
                }
            }
        }

        return null;
    }
}
