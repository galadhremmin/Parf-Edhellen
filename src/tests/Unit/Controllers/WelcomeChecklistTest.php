<?php

namespace Tests\Unit\Controllers;

use App\Models\Account;
use App\Security\AccountManager;
use App\Security\RoleConstants;
use App\Services\WelcomeChecklist;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * The welcome on a new member's own profile: shown to them alone, ticking itself off, gone once done
 * or hidden.
 */
class WelcomeChecklistTest extends TestCase
{
    use DatabaseTransactions;

    private function makeNewMember(array $attributes = []): Account
    {
        /** @var Account */
        $account = Account::factory()->createOne(array_merge([
            'shows_welcome' => true,
            'has_avatar' => false,
            'profile' => '',
            'feature_background_url' => null,
        ], $attributes));
        $account->addMembershipTo(RoleConstants::Users);

        return $account;
    }

    public function test_a_new_registration_gets_the_welcome()
    {
        $account = resolve(AccountManager::class)->createAccount('welcome-'.Str::random(8).'@example.com', null, null, 'A-strong-password-1!', 'Newcomer');

        $this->assertTrue($account->refresh()->shows_welcome);
    }

    public function test_the_welcome_is_on_your_own_profile_only()
    {
        $member = $this->makeNewMember();
        $visitor = $this->makeNewMember();

        $this->actingAs($member)->get(route('author.my-profile'))
            ->assertOk()
            ->assertViewHas('welcome', fn (?array $welcome) => $welcome !== null && $welcome['total'] === 6);

        $this->actingAs($visitor)->get(route('author.profile', ['id' => $member->id, 'nickname' => Str::slug($member->nickname)]))
            ->assertOk()
            ->assertViewHas('welcome', null);
    }

    public function test_existing_members_never_see_it()
    {
        /** @var Account */
        $veteran = Account::factory()->createOne(); // shows_welcome defaults to off
        $veteran->addMembershipTo(RoleConstants::Users);

        $this->actingAs($veteran)->get(route('author.my-profile'))->assertOk()->assertViewHas('welcome', null);
    }

    public function test_steps_tick_themselves_off()
    {
        $member = $this->makeNewMember(['has_avatar' => true, 'profile' => 'Hello, I study Sindarin.']);

        $steps = resolve(WelcomeChecklist::class)->states($member);

        $this->assertSame([
            'name' => true,
            'avatar' => true,
            'introduction' => true,
            'background' => false,
            'contribution' => false,
            'discuss' => false,
        ], $steps);
    }

    public function test_a_placeholder_name_is_a_step_to_take()
    {
        $named = $this->makeNewMember(['nickname' => 'Aerin '.Str::random(4)]);
        $placeholder = $this->makeNewMember(['nickname' => config('ed.default_account_name').' '.random_int(100000, 999999)]);

        $this->assertTrue(WelcomeChecklist::hasChosenName($named));
        $this->assertFalse(WelcomeChecklist::hasChosenName($placeholder));

        // The name step invites them to replace the placeholder, quoting it.
        $this->actingAs($placeholder)->get(route('author.my-profile'))
            ->assertViewHas('welcome', fn (array $welcome) => ! $welcome['steps']['name']['done']
                && str_contains($welcome['steps']['name']['text'], $placeholder->nickname));
    }

    public function test_every_configured_step_needs_a_check()
    {
        config(['ed.welcome.community.unknown' => ['route' => 'home']]);

        $this->expectException(\UnexpectedValueException::class);
        resolve(WelcomeChecklist::class)->states($this->makeNewMember());
    }

    public function test_hiding_it_keeps_it_hidden()
    {
        $member = $this->makeNewMember();

        $this->actingAs($member)->postJson(route('api.account.welcome.dismiss'))->assertNoContent();

        $this->assertFalse($member->refresh()->shows_welcome);
        $this->actingAs($member)->get(route('author.my-profile'))->assertOk()->assertViewHas('welcome', null);
    }

    public function test_a_hidden_welcome_can_be_brought_back()
    {
        $member = $this->makeNewMember();
        $this->actingAs($member)->postJson(route('api.account.welcome.dismiss'))->assertNoContent();

        // The profile offers it back, counting what's left (all but the name, which the factory chose).
        $this->actingAs($member)->get(route('author.my-profile'))
            ->assertViewHas('welcome', null)
            ->assertViewHas('welcomePending', 5);

        $this->actingAs($member)->postJson(route('api.account.welcome.restore'))
            ->assertOk()
            ->assertJsonPath('total', 6);

        $member->refresh();
        $this->assertTrue($member->shows_welcome);
        $this->assertNull($member->welcome_dismissed_at);
    }

    public function test_members_who_never_had_a_welcome_are_not_offered_one()
    {
        /** @var Account */
        $veteran = Account::factory()->createOne(['profile' => '', 'has_avatar' => false]);
        $veteran->addMembershipTo(RoleConstants::Users);

        $this->actingAs($veteran)->get(route('author.my-profile'))->assertViewHas('welcomePending', 0);

        $this->actingAs($veteran)->postJson(route('api.account.welcome.restore'))->assertOk();
        $this->assertFalse($veteran->refresh()->shows_welcome);
    }

    public function test_visitors_are_not_offered_someone_elses_welcome()
    {
        $member = $this->makeNewMember();
        $visitor = $this->makeNewMember();
        resolve(WelcomeChecklist::class)->dismiss($member);

        $this->actingAs($visitor)->get(route('author.profile', ['id' => $member->id, 'nickname' => Str::slug($member->nickname)]))
            ->assertViewHas('welcomePending', 0);
    }
}
