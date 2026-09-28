<?php

namespace Tests\Unit\Services\Senses;

use App\Models\Account;
use App\Models\Concept;
use App\Models\Sense;
use App\Models\SenseConcept;
use App\Models\SenseConceptReview;
use App\Models\SenseTerm;
use App\Repositories\ConceptAuditRepository;
use App\Repositories\ConceptRepository;
use App\Repositories\Enumerations\ConceptReviewReason;
use App\Repositories\Enumerations\ConceptSource;
use App\Security\RoleConstants;
use App\Services\Enumerations\ConceptOutcome;
use App\Services\Enumerations\ConceptRelation;
use App\Services\Senses\SenseConceptEditor;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Unit\Traits\CanBuildSenses;

class SenseConceptEditorTest extends TestCase
{
    use CanBuildSenses;
    use DatabaseTransactions; // ; <-- remedies Visual Studio Code colouring bug

    private const OAK = '12288763-n';

    private const TREE = '13124818-n';

    private SenseConceptEditor $_editor;

    protected function setUp(): void
    {
        parent::setUp();
        $this->requireWordNet();
        $this->_editor = resolve(SenseConceptEditor::class);
    }

    public function test_an_editors_choice_is_locked_against_later_passes()
    {
        $sense = Sense::firstOrFail();
        $concept = resolve(ConceptRepository::class)->forSynset(self::OAK);

        $this->_editor->choose($sense, $concept->id, $this->account(RoleConstants::Administrators));

        $assignment = SenseConcept::where('sense_id', $sense->id)->firstOrFail();
        $this->assertSame($concept->id, $assignment->concept_id);
        $this->assertSame(ConceptSource::EDITOR, $assignment->source);
        $this->assertTrue($assignment->is_locked);
    }

    public function test_a_contributors_choice_is_recorded_but_open_to_revision()
    {
        $sense = Sense::firstOrFail();
        $concept = resolve(ConceptRepository::class)->forSynset(self::OAK);

        $this->_editor->choose($sense, $concept->id, $this->account(RoleConstants::Users));

        $this->assertFalse(SenseConcept::where('sense_id', $sense->id)->firstOrFail()->is_locked);
    }

    public function test_the_choice_and_who_made_it_are_recorded()
    {
        $sense = Sense::firstOrFail();
        $concept = resolve(ConceptRepository::class)->forSynset(self::OAK);
        $account = $this->account(RoleConstants::Administrators);

        $this->_editor->choose($sense, $concept->id, $account);

        $decision = resolve(ConceptAuditRepository::class)->forSense($sense->id)->first();
        $this->assertSame('editor', $decision->decided_by);
        $this->assertSame($account->id, $decision->account_id);
        $this->assertSame($concept->label, $decision->concepts[0]['label']);
    }

    public function test_choosing_nothing_takes_the_meaning_away()
    {
        $sense = Sense::firstOrFail();
        $concept = resolve(ConceptRepository::class)->forSynset(self::OAK);
        $account = $this->account(RoleConstants::Administrators);
        $this->_editor->choose($sense, $concept->id, $account);

        $this->_editor->choose($sense, null, $account);

        $this->assertFalse(SenseConcept::where('sense_id', $sense->id)->exists());
    }

    public function test_a_meaning_picked_from_the_candidates_is_assigned()
    {
        $sense = Sense::with(['word', 'terms'])->firstOrFail();

        $outcome = $this->_editor->chooseSynset($sense, self::OAK, ConceptRelation::SYNONYM,
            $this->account(RoleConstants::Administrators), [self::OAK]);

        $this->assertSame(ConceptOutcome::ASSIGNED, $outcome);
        $this->assertSame('oak', Concept::findOrFail(SenseConcept::where('sense_id', $sense->id)
            ->firstOrFail()->concept_id)->label);
    }

    public function test_a_sense_that_is_a_kind_of_a_candidate_gets_a_concept_of_its_own_beneath_it()
    {
        $sense = $this->normalizedSense();

        $this->_editor->chooseSynset($sense, self::OAK, ConceptRelation::KIND_OF,
            $this->account(RoleConstants::Administrators), [self::OAK]);

        $concept = Concept::findOrFail(SenseConcept::where('sense_id', $sense->id)->firstOrFail()->concept_id);
        $this->assertSame($sense->terms->first()->lemma, $concept->label);
        $this->assertSame('oak', $concept->parent->label);
    }

    public function test_a_dismissed_sense_leaves_the_queue_without_a_concept()
    {
        $sense = Sense::with('word')->firstOrFail();
        resolve(ConceptRepository::class)->review($sense->id, ConceptReviewReason::UNSURE);

        $outcome = $this->_editor->dismiss($sense, $this->account(RoleConstants::Administrators));

        $this->assertSame(ConceptOutcome::NOT_A_CONCEPT, $outcome);
        $this->assertFalse(SenseConceptReview::where('sense_id', $sense->id)->exists());
        $this->assertFalse(SenseConcept::where('sense_id', $sense->id)->exists());
        $this->assertSame(ConceptOutcome::NOT_A_CONCEPT,
            resolve(ConceptAuditRepository::class)->forSense($sense->id)->first()->outcome);
    }

    public function test_a_sense_another_editor_locked_is_left_as_it_was()
    {
        $sense = $this->normalizedSense();
        $this->_editor->chooseSynset($sense, self::OAK, ConceptRelation::SYNONYM,
            $this->account(RoleConstants::Administrators), [self::OAK]);
        $locked = SenseConcept::where('sense_id', $sense->id)->firstOrFail()->concept_id;

        $outcome = $this->_editor->chooseSynset($sense, self::TREE, ConceptRelation::SYNONYM,
            $this->account(RoleConstants::Administrators), [self::TREE]);

        $this->assertSame(ConceptOutcome::LOCKED, $outcome);
        $this->assertSame([$locked], SenseConcept::where('sense_id', $sense->id)->pluck('concept_id')->all());
    }

    /**
     * A sense with the headword the taxonomy places it by, which the meanings it is a kind of are named after.
     */
    private function normalizedSense(): Sense
    {
        $sense = Sense::with(['word', 'terms'])->whereIn('id', SenseTerm::select('sense_id'))->firstOrFail();

        // most of the dictionary is placed already, so clear this one: what is asserted is then what was written here
        SenseConcept::where('sense_id', $sense->id)->delete();

        return $sense;
    }

    private function account(string $role): Account
    {
        $account = Account::factory()->createOne();
        $account->addMembershipTo($role);

        return $account->refresh();
    }
}
