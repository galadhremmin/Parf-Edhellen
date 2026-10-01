<?php

namespace Tests\Unit\Controllers;

use App\Models\Account;
use App\Models\AccountSecurityEvent;
use App\Models\AuthorizationProvider;
use App\Security\AccountManager;
use App\Security\RoleConstants;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Tests\TestCase;

/**
 * An e-mail address belongs to whoever has verified it. An unverified password account must never
 * receive other accounts through linking, and a verified owner can take the address back from it.
 */
class AccountEmailOwnershipTest extends TestCase
{
    use DatabaseTransactions;

    private function makeProvider(): AuthorizationProvider
    {
        $suffix = 'ownership-'.Str::random(8);

        return AuthorizationProvider::create([
            'name' => "Test provider $suffix",
            'name_identifier' => $suffix,
            'logo_file_name' => "$suffix.jpg",
        ]);
    }

    private function makeProviderAccount(string $email, bool $verified): Account
    {
        /** @var Account */
        $account = Account::factory()->createOne([
            'authorization_provider_id' => $this->makeProvider()->id,
            'email' => $email,
            'email_verified_at' => $verified ? Carbon::now() : null,
        ]);
        $account->addMembershipTo(RoleConstants::Users);

        return $account;
    }

    /**
     * An account registered with a password on someone else's address, which it cannot verify.
     */
    private function makeSquatter(string $email): Account
    {
        /** @var Account */
        $account = Account::factory()->createOne([
            'email' => $email,
            'identity' => 'MASTER|'.$email,
            'is_master_account' => true,
            'is_passworded' => true,
            'password' => Hash::make('squatter-password'),
            'email_verified_at' => null,
        ]);
        $account->addMembershipTo(RoleConstants::Users);

        return $account;
    }

    private function email(): string
    {
        return 'ownership-'.Str::random(10).'@example.com';
    }

    private function accountManager(): AccountManager
    {
        return resolve(AccountManager::class);
    }

    public function test_linking_request_is_refused_while_an_unverified_master_holds_the_address()
    {
        $email = $this->email();
        $victim = $this->makeProviderAccount($email, verified: true);
        $other = $this->makeProviderAccount($email, verified: false);
        $this->makeSquatter($email);

        $this->actingAs($victim)
            ->post(route('account.merge'), ['account_id' => [$victim->id, $other->id]])
            ->assertSessionHasErrors('account_id');
    }

    public function test_merging_never_links_into_an_unverified_master()
    {
        $email = $this->email();
        $victim = $this->makeProviderAccount($email, verified: true);
        $other = $this->makeProviderAccount($email, verified: false);
        $squatter = $this->makeSquatter($email);

        try {
            $this->accountManager()->mergeAccounts($victim, collect([$victim, $other]));
            $this->fail('Expected the merge to be refused.');
        } catch (InvalidArgumentException) {
            // expected
        }

        $this->assertSame(0, Account::where('master_account_id', $squatter->id)->count());
    }

    public function test_merging_creates_a_verified_master_from_the_initiator()
    {
        $email = $this->email();
        $other = $this->makeProviderAccount($email, verified: true);
        $initiator = $this->makeProviderAccount($email, verified: true);

        $master = $this->accountManager()->mergeAccounts($initiator, collect([$other, $initiator]));

        $this->assertNotNull($master->email_verified_at);
        $this->assertSame($master->id, $other->refresh()->master_account_id);
        $this->assertSame($master->id, $initiator->refresh()->master_account_id);
    }

    public function test_releasing_strips_the_holder_of_the_address_and_signs_it_out()
    {
        config(['session.driver' => 'database']);

        $email = $this->email();
        $owner = $this->makeProviderAccount($email, verified: true);
        $squatter = $this->makeSquatter($email);
        $rememberToken = $squatter->getRememberToken();
        DB::table('sessions')->insert([
            'id' => Str::random(40),
            'user_id' => $squatter->id,
            'payload' => '',
            'last_activity' => time(),
        ]);

        $this->accountManager()->releaseEmailAddress($squatter, $owner);
        $squatter->refresh();

        $this->assertNull($squatter->email);
        $this->assertFalse((bool) $squatter->is_master_account);
        $this->assertSame('RELEASED|'.$squatter->id, $squatter->identity);
        $this->assertNotSame($rememberToken, $squatter->getRememberToken());
        $this->assertSame(0, DB::table('sessions')->where('user_id', $squatter->id)->count());
        $this->assertNull($this->accountManager()->getMasterAccountByEmail($email));
    }

    public function test_releasing_through_the_security_page_lets_the_owner_link_accounts()
    {
        $email = $this->email();
        $owner = $this->makeProviderAccount($email, verified: true);
        $other = $this->makeProviderAccount($email, verified: true);
        $squatter = $this->makeSquatter($email);

        $this->actingAs($owner)
            ->post(route('account.release-email', ['accountId' => $squatter->id]))
            ->assertRedirect(route('account.security', ['released' => 1]));

        $this->assertNull($squatter->refresh()->email);
        $this->assertTrue(AccountSecurityEvent::where([
            'account_id' => $squatter->id,
            'authenticated_account_id' => $owner->id,
            'type' => 'email-released',
        ])->exists());

        $this->actingAs($owner)
            ->post(route('account.merge'), ['account_id' => [$owner->id, $other->id]])
            ->assertSessionHasNoErrors();
    }

    public function test_security_page_offers_to_release_the_address()
    {
        $email = $this->email();
        $owner = $this->makeProviderAccount($email, verified: true);
        $squatter = $this->makeSquatter($email);

        $this->actingAs($owner)
            ->get(route('account.security'))
            ->assertOk()
            ->assertSee(route('account.release-email', ['accountId' => $squatter->id]))
            ->assertSee('ed-chip--warn', false);
    }

    public function test_login_page_links_to_sign_up()
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee(route('register'));
    }

    public function test_an_account_that_has_not_verified_the_address_can_be_linked()
    {
        // Safe because signing in through it needs a code from the inbox (see SignInChallengeTest).
        $email = $this->email();
        $owner = $this->makeProviderAccount($email, verified: true);
        $other = $this->makeProviderAccount($email, verified: false);

        $master = $this->accountManager()->mergeAccounts($owner, collect([$owner, $other]));

        $this->assertSame($master->id, $other->refresh()->master_account_id);
        $this->assertNull($other->email_verified_at);
    }

    public function test_creating_a_password_requires_a_verified_address()
    {
        $account = $this->makeProviderAccount($this->email(), verified: false);

        $this->actingAs($account)
            ->post(route('account.password'), [
                'new-password' => 'A-strong-password-123!',
                'new-password_confirmation' => 'A-strong-password-123!',
            ])
            ->assertSessionHasErrors('new-password');

        $this->assertNull($account->refresh()->master_account_id);
    }

    public function test_a_verified_account_can_create_a_password_without_a_current_one()
    {
        $email = $this->email();
        $account = $this->makeProviderAccount($email, verified: true);

        $this->actingAs($account)
            ->post(route('account.password'), [
                'new-password' => 'A-strong-password-123!',
                'new-password_confirmation' => 'A-strong-password-123!',
            ])
            ->assertSessionHasNoErrors();

        $master = $account->refresh()->master_account;
        $this->assertNotNull($master);
        $this->assertTrue((bool) $master->is_passworded);
        $this->assertNotNull($master->email_verified_at);
    }

    public function test_release_is_refused_for_an_account_that_does_not_hold_the_address()
    {
        $owner = $this->makeProviderAccount($this->email(), verified: true);
        $stranger = $this->makeSquatter($this->email());

        $this->actingAs($owner)
            ->post(route('account.release-email', ['accountId' => $stranger->id]))
            ->assertNotFound();

        $this->assertNotNull($stranger->refresh()->email);
    }

    public function test_release_is_refused_when_the_holder_has_linked_accounts()
    {
        $email = $this->email();
        $owner = $this->makeProviderAccount($email, verified: true);
        $squatter = $this->makeSquatter($email);
        $linked = $this->makeProviderAccount($email, verified: false);
        $linked->forceFill(['master_account_id' => $squatter->id])->save();

        $this->actingAs($owner)
            ->post(route('account.release-email', ['accountId' => $squatter->id]))
            ->assertSessionHasErrors('release');

        $this->assertSame($email, $squatter->refresh()->email);
    }

    public function test_creating_a_password_is_refused_while_an_unverified_master_holds_the_address()
    {
        $email = $this->email();
        $owner = $this->makeProviderAccount($email, verified: true);
        $this->makeSquatter($email);

        $this->actingAs($owner)
            ->post(route('account.password'), [
                'new-password' => 'A-strong-password-123!',
                'new-password_confirmation' => 'A-strong-password-123!',
            ])
            ->assertSessionHasErrors('new-password');

        $this->assertNull($owner->refresh()->master_account_id);
    }
}
