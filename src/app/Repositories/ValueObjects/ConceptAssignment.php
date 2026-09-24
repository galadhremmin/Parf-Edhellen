<?php

namespace App\Repositories\ValueObjects;

use App\Models\Concept;

/**
 * A concept for one of a sense's terms.
 */
class ConceptAssignment
{
    public function __construct(
        public readonly Concept $concept,
        // the sense term it was assigned for; 0 is the headword
        public readonly int $position,
        // 0-100, as judged by whoever assigned it
        public readonly ?int $confidence = null,
    ) {}
}
