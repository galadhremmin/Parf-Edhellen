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
        public readonly bool $isVerb,
    ) {}
}
