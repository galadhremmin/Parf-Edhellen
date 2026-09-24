<?php

namespace App\Repositories\ValueObjects;

use App\Models\Sense;
use App\Repositories\Enumerations\ConceptSource;
use App\Services\Senses\ConceptApplication;
use App\Services\Senses\ConceptDecision;

/**
 * One decision about a sense, ready to be written to the audit log.
 */
class ConceptAuditEntry
{
    /**
     * @param  Sense  $sense  with `word` loaded
     * @param  string  $decidedBy  a model and its version, or the rule that decided
     * @param  string[]  $candidateSynsetIds  the meanings that were on offer
     */
    public function __construct(
        public readonly Sense $sense,
        public readonly ConceptSource $source,
        public readonly ConceptApplication $application,
        public readonly string $decidedBy,
        public readonly array $candidateSynsetIds = [],
        public readonly ?ConceptDecision $decision = null,
        public readonly ?string $promptHash = null,
        public readonly ?int $accountId = null,
    ) {}
}
