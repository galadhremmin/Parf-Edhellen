<?php

namespace App\Services\Senses;

use Illuminate\Support\Collection;

/**
 * The senses that share a headword, and the meanings they choose between.
 */
class ConceptDecisionRequest
{
    /**
     * @param  Collection<int, ConceptCandidate>  $candidates  most common meaning first
     * @param  Collection<int, SenseEvidence>  $senses
     */
    public function __construct(
        public readonly string $headword,
        // the candidates are meanings of the phrase's head word: "mouth" for "mouth of a river"
        public readonly bool $viaPhraseHead,
        public readonly Collection $candidates,
        public readonly Collection $senses,
    ) {}
}
