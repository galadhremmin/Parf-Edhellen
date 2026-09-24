<?php

namespace App\Services\Senses;

use Illuminate\Support\Collection;

/**
 * The synsets a sense's headword could mean.
 */
class CandidateLookup
{
    /**
     * @param  Collection<int, string>  $synsetIds  most common meaning first
     */
    public function __construct(
        public readonly Collection $synsetIds,
        // what was looked up: the headword, or the head of a phrase WordNet doesn't know as a whole
        public readonly string $lookedUp,
        // "mouth of a river" was looked up as "mouth": the phrase is a kind of its head, not a synonym
        public readonly bool $viaPhraseHead,
    ) {}
}
