<?php

namespace App\Services\Senses;

/**
 * One term of a sense. Senses whose first terms share a key share a headword.
 */
class NormalizedTerm
{
    public function __construct(
        public readonly int $position,
        public readonly string $term,
        public readonly string $key,
        // the cleaned, singular term with its spaces and without "to", e.g. "oak-tree": what WordNet knows it by
        public readonly string $lemma,
        public readonly bool $isVerb,
        // the word the lemmatiser reduced, e.g. "trees" for the key "tree"
        public readonly ?string $reducedFrom = null,
    ) {}
}
