<?php

namespace App\Services\Senses;

use App\Repositories\Enumerations\ConceptReviewReason;
use Illuminate\Support\Collection;

/**
 * One sense waiting for an editor, with everything needed to decide what it means: its own words, the entries that
 * use it, the meanings it could have, and why nobody could settle it.
 */
class SenseReviewItem implements \JsonSerializable
{
    public function __construct(
        public readonly int $senseId,
        public readonly string $sense,
        public readonly ConceptReviewReason $reason,
        // what the judge answered, when it answered but couldn't be trusted
        public readonly ?string $detail,
        public readonly ?int $confidence,
        public readonly int $entries,
        public readonly ConceptDecisionRequest $request,
        /** @var Collection<int, SenseReviewUsage> the first few entries glossed with it */
        public readonly Collection $usages,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return [
            'sense_id' => $this->senseId,
            'sense' => $this->sense,
            'reason' => $this->reason->value,
            'detail' => $this->detail,
            'confidence' => $this->confidence,
            'entries' => $this->entries,
            'headword' => $this->request->headword,
            'via_phrase_head' => $this->request->viaPhraseHead,
            'speeches' => $this->request->senses->first()?->speeches ?? [],
            'usages' => $this->usages->all(),
            'candidates' => $this->request->candidates
                ->map(fn (ConceptCandidate $candidate) => [
                    'synset_id' => $candidate->synsetId,
                    'label' => $candidate->label,
                    'pos' => $candidate->pos->label(),
                    'lexname' => $candidate->lexname,
                    'definition' => $candidate->definition,
                    'synonyms' => $candidate->synonyms,
                    'lineage' => $candidate->lineage,
                ])
                ->all(),
        ];
    }
}
