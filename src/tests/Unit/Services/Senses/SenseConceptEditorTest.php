<?php

namespace Tests\Unit\Services\Senses;

use App\Models\Account;
use App\Models\Sense;
use App\Models\SenseConcept;
use App\Repositories\ConceptAuditRepository;
use App\Repositories\ConceptRepository;
use App\Repositories\Enumerations\ConceptSource;
use App\Security\RoleConstants;
use App\Services\Senses\SenseConceptEditor;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Unit\Traits\CanBuildSenses;

class SenseConceptEditorTest extends TestCase
{
    use CanBuildSenses;
    use DatabaseTransactions; // ; <-- remedies Visual Studio Code colouring bug

    private const OAK = '12288763-n';

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

    private function account(string $role): Account
    {
        $account = Account::factory()->createOne();
        $account->addMembershipTo($role);

        return $account->refresh();
    }
}
