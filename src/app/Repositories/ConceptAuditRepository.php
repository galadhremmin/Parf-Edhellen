<?php

namespace App\Repositories;

use App\Models\Concept;
use App\Models\SenseConceptDecision;
use App\Repositories\Enumerations\ConceptSource;
use App\Repositories\ValueObjects\ConceptAuditEntry;
use Illuminate\Support\Collection;

/**
 * The record of what was decided about each sense's meaning, and by whom. Written once per decision and never
 * changed, so an auditor can see what a model answered even after an editor has overruled it.
 */
class ConceptAuditRepository
{
    public function record(ConceptAuditEntry $entry): SenseConceptDecision
    {
        $decision = $entry->decision;

        return SenseConceptDecision::create([
            'sense_id' => $entry->sense->id,
            'sense' => mb_substr($entry->sense->word->word, 0, 250),
            'source' => $entry->source,
            'outcome' => $entry->application->outcome,
            'decided_by' => $entry->decidedBy,
            'candidate_synset_ids' => $entry->candidateSynsetIds ?: null,
            'synset_ids' => $decision?->synsetIds ?: null,
            'better_word' => $decision?->betterWord,
            'relation' => $decision?->relation?->value,
            'confidence' => $decision?->confidence,
            'concepts' => $entry->application->concepts
                ->map(fn (Concept $concept) => ['id' => $concept->id, 'label' => $concept->label])
                ->values()
                ->all() ?: null,
            'review_reason' => $entry->application->reviewReason,
            'prompt_hash' => $entry->promptHash,
            'account_id' => $entry->accountId,
        ]);
    }

    /**
     * Everything ever decided about one sense, newest first.
     *
     * @return Collection<int, SenseConceptDecision>
     */
    public function forSense(int $senseId): Collection
    {
        return SenseConceptDecision::where('sense_id', $senseId)->orderByDesc('id')->get();
    }

    /**
     * The most recent decisions, newest first, optionally only those made by one source.
     *
     * @return Collection<int, SenseConceptDecision>
     */
    public function recent(int $limit = 50, ?ConceptSource $source = null): Collection
    {
        return SenseConceptDecision::when($source, fn ($query, $source) => $query->where('source', $source))
            ->orderByDesc('id')
            ->limit($limit)
            ->get();
    }
}
