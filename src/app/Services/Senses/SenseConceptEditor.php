<?php

namespace App\Services\Senses;

use App\Models\Account;
use App\Models\Sense;
use App\Models\SenseConcept;
use App\Models\WordNetSynset;
use App\Repositories\ConceptAuditRepository;
use App\Repositories\ConceptRepository;
use App\Repositories\Enumerations\ConceptSource;
use App\Repositories\ValueObjects\ConceptAssignment;
use App\Repositories\ValueObjects\ConceptAuditEntry;
use App\Services\Enumerations\ConceptOutcome;
use App\Services\Enumerations\ConceptRelation;

/**
 * Records the meaning a person chose for a sense. An editor's choice is locked, so no later automated pass may
 * change it; anyone else's is recorded as an assignment a later pass may revisit.
 */
class SenseConceptEditor
{
    // a person who reads the entries and picks a meaning is as sure as this subsystem gets
    private const CERTAIN = 100;

    public function __construct(
        protected readonly ConceptRepository $_concepts,
        protected readonly ConceptAuditRepository $_audit,
    ) {}

    /**
     * Assigns the chosen concept to the sense, or removes what was chosen before when none is given.
     *
     * @param  string[]  $offered  the meanings the person was shown, for the audit log
     */
    public function choose(Sense $sense, ?int $conceptId, Account $account, array $offered = [],
        ?ConceptDecision $decision = null): ConceptOutcome
    {
        if ($conceptId === null) {
            return $this->clear($sense, $account);
        }

        $concept = $this->_concepts->find($conceptId);
        if ($concept === null) {
            return ConceptOutcome::NOT_A_CONCEPT;
        }

        $locked = $account->isAdministrator();
        if (! $this->_concepts->assign($sense->id, ConceptSource::EDITOR, collect([new ConceptAssignment($concept, 0)]))) {
            // another editor got here first and locked it: say so rather than report a change that never happened
            $this->_audit->record(new ConceptAuditEntry($sense, ConceptSource::EDITOR,
                ConceptApplication::of(ConceptOutcome::LOCKED, $concept), $this->decidedBy($account),
                candidateSynsetIds: $offered, decision: $decision, accountId: $account->id));

            return ConceptOutcome::LOCKED;
        }

        if ($locked) {
            // an editor has decided: no rule, model or backfill may overrule it
            SenseConcept::where('sense_id', $sense->id)->where('concept_id', $concept->id)->update(['is_locked' => true]);
        }

        $this->_concepts->clearReview($sense->id);
        $application = ConceptApplication::of(ConceptOutcome::ASSIGNED, $concept);
        $this->_audit->record(new ConceptAuditEntry($sense, ConceptSource::EDITOR, $application,
            $this->decidedBy($account), candidateSynsetIds: $offered, decision: $decision, accountId: $account->id));

        return ConceptOutcome::ASSIGNED;
    }

    /**
     * Assigns the WordNet meaning an editor picked from the candidates. A synonym is the meaning itself; a kind of it
     * gets a concept of its own underneath, as "mallorn" sits under "tree".
     *
     * @param  Sense  $sense  with `word` and `terms` loaded
     * @param  string[]  $offered  the meanings the editor was shown, for the audit log
     */
    public function chooseSynset(Sense $sense, string $synsetId, ConceptRelation $relation, Account $account,
        array $offered = []): ConceptOutcome
    {
        if (WordNetSynset::find($synsetId) === null) {
            return ConceptOutcome::NOT_A_CONCEPT;
        }

        $concept = $this->_concepts->forSynset($synsetId);
        if ($relation === ConceptRelation::KIND_OF) {
            $head = $sense->terms->first();
            if ($head === null) {
                // a sense nobody has normalised has no headword to hang a narrower concept on
                return ConceptOutcome::LEFT_AS_IS;
            }

            $concept = $this->_concepts->forWord($head->lemma, $concept, $head->term_key);
        }

        return $this->choose($sense, $concept->id, $account, $offered,
            new ConceptDecision($sense->id, [$synsetId], null, $relation, self::CERTAIN));
    }

    /**
     * Records that a sense means nothing the taxonomy can hold — a name, a grammatical label, an Elvish word left
     * untranslated — and takes it off the list for good.
     *
     * @param  Sense  $sense  with `word` loaded
     * @param  string[]  $offered
     */
    public function dismiss(Sense $sense, Account $account, array $offered = []): ConceptOutcome
    {
        $this->_concepts->clearReview($sense->id);
        $this->_audit->record(new ConceptAuditEntry($sense, ConceptSource::EDITOR,
            ConceptApplication::of(ConceptOutcome::NOT_A_CONCEPT), $this->decidedBy($account),
            candidateSynsetIds: $offered, accountId: $account->id));

        return ConceptOutcome::NOT_A_CONCEPT;
    }

    /**
     * Takes away what a person chose, leaving the sense to be decided again.
     */
    private function decidedBy(Account $account): string
    {
        return $account->isAdministrator() ? 'editor' : 'contributor';
    }

    private function clear(Sense $sense, Account $account): ConceptOutcome
    {
        SenseConcept::where('sense_id', $sense->id)->where('source', ConceptSource::EDITOR)->delete();

        $this->_audit->record(new ConceptAuditEntry($sense, ConceptSource::EDITOR,
            ConceptApplication::of(ConceptOutcome::NOT_A_CONCEPT), 'editor: removed', accountId: $account->id));

        return ConceptOutcome::NOT_A_CONCEPT;
    }
}
