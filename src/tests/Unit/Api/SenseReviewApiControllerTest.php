<?php

namespace Tests\Unit\Api;

use App\Models\Account;
use App\Models\LexicalEntry;
use App\Models\Sense;
use App\Models\SenseConcept;
use App\Models\SenseConceptReview;
use App\Models\SenseTerm;
use App\Repositories\ConceptRepository;
use App\Repositories\Enumerations\ConceptReviewReason;
use App\Security\RoleConstants;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Unit\Traits\CanBuildSenses;

class SenseReviewApiControllerTest extends TestCase
{
    use CanBuildSenses;
    use DatabaseTransactions; // ; <-- remedies Visual Studio Code colouring bug

    private const OAK = '12288763-n';

    protected function setUp(): void
    {
        parent::setUp();
        $this->requireWordNet();

        // only what these tests put in the queue should come out of it
        SenseConceptReview::query()->delete();
    }

    public function test_the_queue_is_closed_to_everyone_but_administrators()
    {
        $response = $this->actingAs($this->account(RoleConstants::Users))
            ->getJson(route('api.sense-review.next'));

        $response->assertForbidden();
    }

    public function test_an_administrator_is_given_the_next_sense_and_the_queue_behind_it()
    {
        $sense = $this->waitingSense();

        $response = $this->actingAs($this->account(RoleConstants::Administrators))
            ->getJson(route('api.sense-review.next'));

        $response->assertSuccessful();
        $response->assertJsonPath('sense.sense_id', $sense->id);
        $response->assertJsonPath('waiting', 1);
        $response->assertJsonPath('by_reason.unsure', 1);
    }

    public function test_a_chosen_meaning_is_assigned_and_the_sense_leaves_the_queue()
    {
        $sense = $this->waitingSense();

        $response = $this->actingAs($this->account(RoleConstants::Administrators))
            ->postJson(route('api.sense-review.decide', ['id' => $sense->id]), [
                'synset_id' => self::OAK,
                'relation' => 'synonym',
                'offered' => [self::OAK],
            ]);

        $response->assertSuccessful();
        $response->assertJsonPath('assigned', true);
        $this->assertFalse(SenseConceptReview::where('sense_id', $sense->id)->exists());
        $this->assertTrue(SenseConcept::where('sense_id', $sense->id)->where('is_locked', true)->exists());
    }

    public function test_a_dismissed_sense_leaves_the_queue_with_no_meaning()
    {
        $sense = $this->waitingSense();

        $response = $this->actingAs($this->account(RoleConstants::Administrators))
            ->postJson(route('api.sense-review.decide', ['id' => $sense->id]), ['dismiss' => true]);

        $response->assertSuccessful();
        $response->assertJsonPath('outcome', 'not a concept');
        $this->assertFalse(SenseConceptReview::where('sense_id', $sense->id)->exists());
        $this->assertFalse(SenseConcept::where('sense_id', $sense->id)->exists());
    }

    public function test_a_decision_that_says_nothing_is_refused()
    {
        $sense = $this->waitingSense();

        $response = $this->actingAs($this->account(RoleConstants::Administrators))
            ->postJson(route('api.sense-review.decide', ['id' => $sense->id]), []);

        $response->assertStatus(422);
        $this->assertTrue(SenseConceptReview::where('sense_id', $sense->id)->exists());
    }

    public function test_a_meaning_that_was_never_on_offer_is_refused()
    {
        $sense = $this->waitingSense();

        $response = $this->actingAs($this->account(RoleConstants::Administrators))
            ->postJson(route('api.sense-review.decide', ['id' => $sense->id]), ['synset_id' => 'not-a-synset']);

        $response->assertStatus(422);
    }

    public function test_a_mis_transcribed_sense_is_reworded_and_its_entries_move()
    {
        $sense = $this->waitingSense();
        $entryIds = LexicalEntry::active()->where('sense_id', $sense->id)->pluck('id');

        $response = $this->actingAs($this->account(RoleConstants::Administrators))
            ->postJson(route('api.sense-review.reword', ['id' => $sense->id]), ['sense' => 'number']);

        $response->assertSuccessful();
        $response->assertJsonPath('outcome', 'reworded');
        $response->assertJsonPath('result.entries', $entryIds->count());
        $this->assertFalse(SenseConceptReview::where('sense_id', $sense->id)->exists());
        $this->assertFalse(LexicalEntry::active()->where('sense_id', $sense->id)->exists());
    }

    public function test_rewording_to_the_wording_it_already_has_is_refused()
    {
        $sense = $this->waitingSense();

        $response = $this->actingAs($this->account(RoleConstants::Administrators))
            ->postJson(route('api.sense-review.reword', ['id' => $sense->id]), ['sense' => $sense->word->word]);

        $response->assertStatus(422);
        $this->assertTrue(SenseConceptReview::where('sense_id', $sense->id)->exists());
    }

    public function test_only_administrators_may_reword_a_sense()
    {
        $sense = $this->waitingSense();

        $this->actingAs($this->account(RoleConstants::Users))
            ->postJson(route('api.sense-review.reword', ['id' => $sense->id]), ['sense' => 'number'])
            ->assertForbidden();
    }

    /**
     * A normalised sense in use, waiting for an editor and placed by nobody yet.
     */
    private function waitingSense(): Sense
    {
        $sense = Sense::with(['word', 'terms'])->whereIn('id', SenseTerm::select('sense_id'))->firstOrFail();
        SenseConcept::where('sense_id', $sense->id)->delete();
        resolve(ConceptRepository::class)->review($sense->id, ConceptReviewReason::UNSURE);

        return $sense;
    }

    private function account(string $role): Account
    {
        /** @var Account */
        $account = Account::factory()->createOne(['email_verified_at' => Carbon::now()]);
        // real accounts hold the default Users role as well; without it the InvalidUserGate middleware treats the
        // account as banned, and a 403 would prove nothing about the role being tested
        $account->addMembershipTo(RoleConstants::Users);
        if ($role !== RoleConstants::Users) {
            $account->addMembershipTo($role);
        }

        return $account->refresh();
    }
}
