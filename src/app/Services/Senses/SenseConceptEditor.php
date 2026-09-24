<?php

namespace App\Services\Senses;

use App\Models\Account;
use App\Models\Sense;
use App\Models\SenseConcept;
use App\Repositories\ConceptAuditRepository;
use App\Repositories\ConceptRepository;
use App\Repositories\Enumerations\ConceptSource;
use App\Repositories\ValueObjects\ConceptAssignment;
use App\Repositories\ValueObjects\ConceptAuditEntry;
use App\Services\Enumerations\ConceptOutcome;

/**
 * Records the meaning a person chose for a sense. An editor's choice is locked, so no later automated pass may
 * change it; anyone else's is recorded as an assignment a later pass may revisit.
 */
class SenseConceptEditor
{
    public function __construct(
        protected readonly ConceptRepository $_concepts,
        protected readonly ConceptAuditRepository $_audit,
    ) {}

    /**
     * Assigns the chosen concept to the sense, or removes what was chosen before when none is given.
     */
    public function choose(Sense $sense, ?int $conceptId, Account $account): ConceptOutcome
    {
        if ($conceptId === null) {
            return $this->clear($sense, $account);
        }

        $concept = $this->_concepts->find($conceptId);
        if ($concept === null) {
            return ConceptOutcome::NOT_A_CONCEPT;
        }

        $locked = $account->isAdministrator();
        $this->_concepts->assign($sense->id, ConceptSource::EDITOR, collect([new ConceptAssignment($concept, 0)]));

        if ($locked) {
            // an editor has decided: no rule, model or backfill may overrule it
            SenseConcept::where('sense_id', $sense->id)->where('concept_id', $concept->id)->update(['is_locked' => true]);
        }

        $this->_concepts->clearReview($sense->id);
        $application = ConceptApplication::of(ConceptOutcome::ASSIGNED, $concept);
        $this->_audit->record(new ConceptAuditEntry($sense, ConceptSource::EDITOR, $application,
            $locked ? 'editor' : 'contributor', accountId: $account->id));

        return ConceptOutcome::ASSIGNED;
    }

    /**
     * Takes away what a person chose, leaving the sense to be decided again.
     */
    private function clear(Sense $sense, Account $account): ConceptOutcome
    {
        SenseConcept::where('sense_id', $sense->id)->where('source', ConceptSource::EDITOR)->delete();

        $this->_audit->record(new ConceptAuditEntry($sense, ConceptSource::EDITOR,
            ConceptApplication::of(ConceptOutcome::NOT_A_CONCEPT), 'editor: removed', accountId: $account->id));

        return ConceptOutcome::NOT_A_CONCEPT;
    }
}
