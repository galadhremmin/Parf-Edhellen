<?php

namespace App\Services\Senses;

use App\Services\Enumerations\WordNetPos;

/**
 * One meaning a headword could have, described well enough to choose between meanings.
 */
class ConceptCandidate
{
    /**
     * @param  string[]  $synonyms  other words for the same meaning
     * @param  string[]  $lineage  what it is a kind of, nearest first: tree, woody plant, …
     */
    public function __construct(
        public readonly string $synsetId,
        public readonly string $label,
        public readonly WordNetPos $pos,
        // WordNet's broad category, e.g. noun.plant
        public readonly string $lexname,
        public readonly string $definition,
        public readonly array $synonyms,
        public readonly array $lineage,
    ) {}
}
