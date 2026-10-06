<?php

namespace Tests\Unit\Services\Senses;

use App\Models\Account;
use App\Models\LexicalEntry;
use App\Models\Sense;
use App\Models\SenseConceptDecision;
use App\Models\SenseConceptReview;
use App\Repositories\ConceptRepository;
use App\Repositories\Enumerations\ConceptReviewReason;
use App\Security\RoleConstants;
use App\Services\Enumerations\ConceptOutcome;
use App\Services\Senses\SenseRewording;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * Rewording is for the senses that are no meaning at all: "card" over a column of Sindarin numerals is the lexicon's
 * abbreviation for "cardinal", and belongs with "number".
 */
class SenseRewordingTest extends TestCase
{
    use DatabaseTransactions; // ; <-- remedies Visual Studio Code colouring bug

    private SenseRewording $_rewording;

    protected function setUp(): void
    {
        parent::setUp();
        $this->_rewording = resolve(SenseRewording::class);
    }

    public function test_every_entry_glossed_with_it_moves_to_the_corrected_wording()
    {
        $sense = $this->senseInUse();
        $entryIds = LexicalEntry::active()->where('sense_id', $sense->id)->pluck('id');

        $result = $this->_rewording->reword($sense, 'number', $this->editor());

        $this->assertTrue($result->wasMoved());
        $this->assertSame($entryIds->count(), $result->entries);
        $this->assertSame('number', Sense::with('word')->findOrFail($result->senseId)->word->word);
        $this->assertSame([$result->senseId],
            LexicalEntry::whereIn('id', $entryIds)->distinct()->pluck('sense_id')->all());
        $this->assertFalse(LexicalEntry::active()->where('sense_id', $sense->id)->exists());
    }

    public function test_the_old_wording_leaves_the_queue_and_the_decision_is_recorded()
    {
        $sense = $this->senseInUse();
        resolve(ConceptRepository::class)->review($sense->id, ConceptReviewReason::UNSURE);

        $this->_rewording->reword($sense, 'number', $this->editor());

        $this->assertFalse(SenseConceptReview::where('sense_id', $sense->id)->exists());
        $decision = SenseConceptDecision::where('sense_id', $sense->id)->latest('id')->firstOrFail();
        $this->assertSame(ConceptOutcome::REWORDED, $decision->outcome);
        $this->assertSame('number', $decision->better_word);
        $this->assertSame('editor', $decision->decided_by);
    }

    public function test_the_wording_it_already_has_is_refused()
    {
        $sense = $this->senseInUse();

        $result = $this->_rewording->reword($sense, strtoupper($sense->word->word), $this->editor());

        $this->assertFalse($result->wasMoved());
        $this->assertSame(0, $result->entries);
        $this->assertTrue(LexicalEntry::active()->where('sense_id', $sense->id)->exists());
    }

    public function test_a_sense_nothing_is_glossed_with_is_refused()
    {
        $sense = Sense::with('word')->whereNotIn('id', LexicalEntry::active()->select('sense_id'))->firstOrFail();

        $result = $this->_rewording->reword($sense, 'number', $this->editor());

        $this->assertFalse($result->wasMoved());
        $this->assertNotNull($result->refusal);
    }

    /**
     * A sense only one entry is glossed with: enough to prove every entry moves, without rewriting a column of them.
     */
    private function senseInUse(): Sense
    {
        $senseId = LexicalEntry::active()
            ->selectRaw('sense_id')
            ->groupBy('sense_id')
            ->havingRaw('COUNT(*) = 1')
            ->value('sense_id');

        if ($senseId === null) {
            $this->markTestSkipped('No sense in the dictionary is glossed on a single entry.');
        }

        return Sense::with('word')->findOrFail($senseId);
    }

    private function editor(): Account
    {
        $account = Account::factory()->createOne();
        $account->addMembershipTo(RoleConstants::Administrators);

        return $account->refresh();
    }
}
