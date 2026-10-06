<?php

namespace Tests\Unit\Controllers;

use App\Mail\VerifyEmailAddressMail;
use App\Models\Account;
use App\Security\RoleConstants;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

/**
 * The "confirm your e-mail address" interstitial: an unconfirmed account can browse like a visitor,
 * but every personal page waits until the address is proven by the code or the link.
 */
class AccountVerificationControllerTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
    }

    private function makeUnverifiedAccount(): Account
    {
        /** @var Account */
        $account = Account::factory()->unverified()->createOne();
        $account->addMembershipTo(RoleConstants::Users);

        return $account;
    }

    /**
     * @return string[] every code e-mailed so far, oldest first
     */
    private function sentCodes(): array
    {
        return Mail::queued(VerifyEmailAddressMail::class)
            ->map(fn (VerifyEmailAddressMail $mail) => (fn () => $this->_code)->call($mail))
            ->values()
            ->all();
    }

    public function test_personal_pages_wait_behind_the_interstitial()
    {
        $account = $this->makeUnverifiedAccount();

        foreach (['contribution.index', 'author.my-profile', 'author.edit-profile', 'account.security', 'discuss.create'] as $route) {
            $this->actingAs($account)->get(route($route))->assertRedirect(route('verification.notice'));
        }
    }

    public function test_background_requests_on_public_pages_keep_working()
    {
        // A dictionary page asks which word lists an entry is in; that must not fail mid-browse.
        $account = $this->makeUnverifiedAccount();

        $this->actingAs($account)
            ->postJson('/api/v'.config('ed.api_version').'/word-lists/check-membership', ['lexical_entry_ids' => [1]])
            ->assertOk();
    }

    public function test_public_pages_stay_open_and_the_menu_asks_to_confirm()
    {
        $account = $this->makeUnverifiedAccount();

        $this->actingAs($account)->get(route('home'))
            ->assertOk()
            ->assertSee(route('verification.notice'))
            ->assertSee('ed-user-menu__confirm', false);
    }

    public function test_arriving_at_the_interstitial_sends_one_e_mail()
    {
        $account = $this->makeUnverifiedAccount();

        $this->actingAs($account)->get(route('verification.notice'))->assertOk()->assertSee($account->email);
        $this->actingAs($account)->get(route('verification.notice'))->assertOk();

        Mail::assertQueued(VerifyEmailAddressMail::class, 1);
    }

    public function test_asking_again_right_away_waits_for_the_cooldown()
    {
        $account = $this->makeUnverifiedAccount();
        $this->actingAs($account)->get(route('verification.notice'));

        $this->actingAs($account)->post(route('account.resent-verification'))
            ->assertRedirect(route('verification.notice'))
            ->assertSessionHas('status', fn (string $status) => str_contains($status, 'a moment ago'));

        Mail::assertQueued(VerifyEmailAddressMail::class, 1);
    }

    public function test_the_code_confirms_the_address_and_continues_where_they_were_heading()
    {
        $account = $this->makeUnverifiedAccount();
        $this->actingAs($account)->get(route('contribution.index')); // held, and remembered
        $this->actingAs($account)->get(route('verification.notice'));
        [$code] = $this->sentCodes();

        $this->actingAs($account)->post(route('verification.check'), ['code' => $code === '000000' ? '111111' : '000000'])
            ->assertSessionHasErrors('code');
        $this->assertFalse($account->refresh()->hasVerifiedEmail());

        $this->actingAs($account)->post(route('verification.check'), ['code' => $code])
            ->assertRedirect(route('contribution.index'));
        $this->assertTrue($account->refresh()->hasVerifiedEmail());
    }

    public function test_without_a_destination_the_code_leads_to_the_profile()
    {
        $account = $this->makeUnverifiedAccount();
        $this->actingAs($account)->get(route('verification.notice'));
        [$code] = $this->sentCodes();

        $this->actingAs($account)->post(route('verification.check'), ['code' => $code])
            ->assertRedirect(route('author.my-profile'));
    }

    public function test_the_link_confirms_the_address_and_grants_discuss()
    {
        $account = $this->makeUnverifiedAccount();
        $this->assertFalse($account->memberOf(RoleConstants::Discuss));

        $this->actingAs($account)->get(URL::signedRoute('verification.verify', [
            'id' => $account->getKey(),
            'hash' => sha1($account->getEmailForVerification()),
        ]))->assertRedirect(route('author.my-profile'));

        $account->refresh();
        $this->assertTrue($account->hasVerifiedEmail());
        $this->assertTrue($account->memberOf(RoleConstants::Discuss));
    }

    public function test_a_verified_account_passes_straight_through_the_interstitial()
    {
        /** @var Account */
        $account = Account::factory()->createOne();
        $account->addMembershipTo(RoleConstants::Users);

        $this->actingAs($account)->get(route('verification.notice'))->assertRedirect(route('author.my-profile'));
        Mail::assertNothingQueued();
    }
}
