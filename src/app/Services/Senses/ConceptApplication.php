<?php

namespace App\Services\Senses;

use App\Models\Concept;
use App\Repositories\Enumerations\ConceptReviewReason;
use App\Services\Enumerations\ConceptOutcome;
use Illuminate\Support\Collection;

/**
 * What came of a decision: the concepts it led to, or the reason it went to an editor instead.
 */
class ConceptApplication
{
    /**
     * @param  Collection<int, Concept>  $concepts
     */
    public function __construct(
        public readonly ConceptOutcome $outcome,
        public readonly Collection $concepts,
        public readonly ?ConceptReviewReason $reviewReason = null,
    ) {}

    public static function of(ConceptOutcome $outcome, ?Concept $concept = null): self
    {
        return new self($outcome, collect(array_filter([$concept])));
    }

    public static function review(ConceptReviewReason $reason): self
    {
        return new self(ConceptOutcome::NEEDS_REVIEW, collect(), $reason);
    }
}
