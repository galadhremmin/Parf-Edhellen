<?php

namespace App\Services\Senses;

/**
 * What a sense says about its meaning: its own words, and the entries that use it.
 */
class SenseEvidence
{
    /**
     * @param  string[]  $speeches  the parts of speech its entries record
     * @param  string[]  $entries  e.g. "Quenya alda (noun): tree"
     */
    public function __construct(
        public readonly int $senseId,
        public readonly string $sense,
        public readonly array $speeches,
        public readonly array $entries,
    ) {}
}
