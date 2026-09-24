<?php

namespace App\Services\Senses;

use App\Interfaces\IJudgesSenseConcepts;
use App\Models\Concept;
use App\Models\Sense;
use App\Repositories\ConceptAuditRepository;
use App\Repositories\ConceptRepository;
use App\Repositories\Enumerations\ConceptReviewReason;
use App\Repositories\Enumerations\ConceptSource;
use App\Repositories\ValueObjects\ConceptAssignment;
use App\Repositories\ValueObjects\ConceptAuditEntry;
use App\Services\Enumerations\ConceptOutcome;
use App\Services\Enumerations\SenseKind;

/**
 * Works out what a sense means. Each step is cheaper than the last one is expensive, and the first that settles the
 * sense wins, so a judge is only asked about senses the dictionary's own data can't place. Every step's outcome is
 * written to the audit log, whether a rule, a model or nobody decided it.
 */
class SenseConceptResolver
{
    public function __construct(
        protected readonly ConceptRepository $_concepts,
        protected readonly ConceptAuditRepository $_audit,
        protected readonly SenseClassifier $_classifier,
        protected readonly NameConcepts $_names,
        protected readonly ConceptCandidateFinder $_finder,
        protected readonly ConceptDecisionPrompt $_prompt,
        protected readonly IJudgesSenseConcepts $_judge,
        protected readonly ConceptDecisionApplier $_applier,
    ) {}

    /**
     * @param  Sense  $sense  with `word`, `terms` and `lexical_entries` loaded
     * @param  bool  $mayAsk  false leaves what the rules can't place for an editor or a backfill, asking nobody, which
     *                        is what a pass over the whole dictionary wants
     * @param  bool  $mayQueue  false records nothing unless a concept is assigned, so a pass after a rule change can
     *                          place senses without undoing a judgement already made about them
     */
    public function resolve(Sense $sense, bool $mayAsk = true, bool $mayQueue = true): ConceptOutcome
    {
        if ($sense->concepts()->exists()) {
            return ConceptOutcome::ALREADY_ASSIGNED;
        }

        $kind = $this->_classifier->classify($sense);
        if ($kind === SenseKind::NAME) {
            return $this->byRule($sense, $this->_concepts->forSynset($this->_names->synsetIdFor($sense)), $kind->value);
        }

        if ($kind !== SenseKind::LEXICAL) {
            if (! $mayQueue) {
                return ConceptOutcome::LEFT_AS_IS;
            }

            $this->_concepts->clearReview($sense->id);

            return $this->record($sense, ConceptSource::RULE, ConceptApplication::of(ConceptOutcome::NOT_A_CONCEPT), $kind->value);
        }

        $lookup = $this->_finder->lookUp($sense);
        $synsetId = $this->_finder->selfEvident($lookup);
        if ($synsetId !== null) {
            return $this->byRule($sense, $this->_concepts->forSynset($synsetId), 'the headword\'s only meaning',
                $lookup->synsetIds->all());
        }

        $settled = $this->_concepts->settledForHeadword($sense->terms->first()->term_key);
        if ($settled !== null) {
            return $this->byRule($sense, $settled, 'the headword\'s settled meaning', $lookup->synsetIds->all(),
                ConceptOutcome::INHERITED);
        }

        if (! $mayAsk) {
            if (! $mayQueue) {
                return ConceptOutcome::LEFT_AS_IS;
            }

            $this->_concepts->review($sense->id, ConceptReviewReason::NOT_JUDGED);

            return $this->record($sense, ConceptSource::RULE, ConceptApplication::review(ConceptReviewReason::NOT_JUDGED),
                'left to be judged', $lookup->synsetIds->all());
        }

        return $this->byJudge($sense, $lookup);
    }

    /**
     * Puts the question to the judge, and records what it answered even when the answer can't be used.
     */
    private function byJudge(Sense $sense, CandidateLookup $lookup): ConceptOutcome
    {
        $request = $this->_prompt->request(collect([$sense]));
        $candidateIds = $request->candidates->pluck('synsetId')->all();
        $promptHash = sha1($this->_prompt->prompt(collect([$request])));
        $decision = $this->_judge->decide(collect([$request]))->firstWhere('senseId', $sense->id);

        if ($decision === null) {
            $this->_concepts->review($sense->id, ConceptReviewReason::NOT_JUDGED);

            return $this->record($sense, ConceptSource::GEMINI, ConceptApplication::review(ConceptReviewReason::NOT_JUDGED),
                $this->_judge->name(), $candidateIds, promptHash: $promptHash);
        }

        $application = $this->_applier->apply($sense, $decision, ConceptSource::GEMINI, $candidateIds);

        return $this->record($sense, ConceptSource::GEMINI, $application, $this->_judge->name(), $candidateIds,
            $decision, $promptHash);
    }

    /**
     * Assigns a concept the rules are sure of, and takes the sense off the editors' list.
     *
     * @param  string[]  $candidateIds
     */
    private function byRule(Sense $sense, Concept $concept, string $decidedBy, array $candidateIds = [],
        ConceptOutcome $outcome = ConceptOutcome::ASSIGNED_BY_RULE): ConceptOutcome
    {
        $assigned = $this->_concepts->assign($sense->id, ConceptSource::RULE, collect([new ConceptAssignment($concept, 0)]));
        if ($assigned) {
            $this->_concepts->clearReview($sense->id);
        }

        $application = $assigned
            ? ConceptApplication::of($outcome, $concept)
            : ConceptApplication::of(ConceptOutcome::LOCKED, $concept);

        return $this->record($sense, ConceptSource::RULE, $application, $decidedBy, $candidateIds);
    }

    /**
     * @param  string[]  $candidateIds
     */
    private function record(Sense $sense, ConceptSource $source, ConceptApplication $application, string $decidedBy,
        array $candidateIds = [], ?ConceptDecision $decision = null, ?string $promptHash = null): ConceptOutcome
    {
        $this->_audit->record(new ConceptAuditEntry($sense, $source, $application, $decidedBy, $candidateIds,
            $decision, $promptHash));

        return $application->outcome;
    }
}
