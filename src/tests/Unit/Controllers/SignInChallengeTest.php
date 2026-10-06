<?php

namespace Tests\Unit\Controllers;

use App\Mail\SignInCodeMail;
use App\Models\Account;
use App\Models\AuthorizationProvider;
use App\Security\Identity\SocialiteIdentityProvider;
use App\Security\RoleConstants;
use App\Security\SignInChallenge;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use Mockery;
use Tests\TestCase;

/**
 * Verify on first use: signing in through a linked account that never verified its address needs a
 * code from the inbox, entered in the same browser.
 */
class SignInChallengeTest extends TestCase
{
    use DatabaseTransactions;

    private AuthorizationProvider $_provider;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();

        $suffix = 'challenge-'.Str::lower(Str::random(8));
        $this->_provider = AuthorizationProvider::create([
            'name' => "Test provider $suffix",
            'name_identifier' => $suffix,
            'logo_file_name' => "$suffix.jpg",
        ]);
        config(['ed.identity_providers.'.$suffix => SocialiteIdentityProvider::class]);
    }

    /**
     * A principal account with a provider account linked to it, as linking leaves them.
     *
     * @return array{0: Account, 1: Account} the master, then the linked provider account
     */
    private function makeLinkedPair(bool $linkedVerified): array
    {
        $email = 'challenge-'.Str::random(10).'@example.com';

        /** @var Account */
        $master = Account::factory()->createOne([
            'email' => $email,
            'identity' => 'MASTER|'.$email,
            'is_master_account' => true,
            'email_verified_at' => Carbon::now(),
        ]);
        $master->addMembershipTo(RoleConstants::Users);

        /** @var Account */
        $linked = Account::factory()->createOne([
            'email' => $email,
            'authorization_provider_id' => $this->_provider->id,
            'identity' => 'subject-'.Str::random(12),
            'master_account_id' => $master->id,
            'email_verified_at' => $linkedVerified ? Carbon::now() : null,
        ]);
        $linked->addMembershipTo(RoleConstants::Users);

        return [$master, $linked];
    }

    /**
     * Signs in through the provider as `$account`, the way the OAuth callback sees it.
     */
    private function signInWithProvider(Account $account)
    {
        $providerUser = (new SocialiteUser)->map([
            'id' => $account->identity,
            'email' => $account->email,
            'name' => $account->nickname,
        ]);
        $driver = Mockery::mock();
        $driver->shouldReceive('user')->andReturn($providerUser);
        Socialite::shouldReceive('driver')->with($this->_provider->name_identifier)->andReturn($driver);

        return $this->get('/federated-auth/callback/'.$this->_provider->name_identifier);
    }

    private function sentCode(): string
    {
        $code = null;
        Mail::assertQueued(SignInCodeMail::class, function (SignInCodeMail $mail) use (&$code) {
            $code = (fn () => $this->_code)->call($mail);

            return true;
        });

        return $code;
    }

    public function test_only_linked_unverified_accounts_are_challenged()
    {
        $challenge = resolve(SignInChallenge::class);
        [$master, $unverified] = $this->makeLinkedPair(linkedVerified: false);
        [, $verified] = $this->makeLinkedPair(linkedVerified: true);

        $this->assertTrue($challenge->isRequiredFor($unverified));
        $this->assertFalse($challenge->isRequiredFor($verified));
        $this->assertFalse($challenge->isRequiredFor($master));
    }

    public function test_a_verified_linked_account_signs_straight_in()
    {
        [$master, $linked] = $this->makeLinkedPair(linkedVerified: true);

        $this->signInWithProvider($linked)->assertRedirect();

        $this->assertAuthenticatedAs($master);
        Mail::assertNothingQueued();
    }

    public function test_an_unverified_linked_account_must_enter_the_e_mailed_code()
    {
        [$master, $linked] = $this->makeLinkedPair(linkedVerified: false);

        $this->signInWithProvider($linked)->assertRedirect(route('auth.confirm-sign-in'));
        $this->assertGuest();
        $code = $this->sentCode();

        $this->get(route('auth.confirm-sign-in'))->assertOk()->assertSee($linked->nickname);

        $this->post(route('auth.confirm-sign-in.check'), ['code' => $code === '000000' ? '111111' : '000000'])
            ->assertSessionHasErrors('code');
        $this->assertGuest();

        $this->post(route('auth.confirm-sign-in.check'), ['code' => substr($code, 0, 3).' '.substr($code, 3)])
            ->assertRedirect();

        $this->assertAuthenticatedAs($master);
        $this->assertNotNull($linked->refresh()->email_verified_at);
    }

    public function test_too_many_wrong_codes_close_the_challenge()
    {
        [, $linked] = $this->makeLinkedPair(linkedVerified: false);
        $this->signInWithProvider($linked);
        $code = $this->sentCode();
        $wrong = $code === '000000' ? '111111' : '000000';

        for ($i = 1; $i < SignInChallenge::MAX_ATTEMPTS; $i++) {
            $this->post(route('auth.confirm-sign-in.check'), ['code' => $wrong])->assertSessionHasErrors('code');
        }
        $this->post(route('auth.confirm-sign-in.check'), ['code' => $wrong])->assertRedirect(route('login'));

        // Even the right code is refused now: the person has to sign in again.
        $this->post(route('auth.confirm-sign-in.check'), ['code' => $code])->assertRedirect(route('login'));
        $this->assertGuest();
        $this->assertNull($linked->refresh()->email_verified_at);
    }

    public function test_the_code_only_works_in_the_browser_that_asked_for_it()
    {
        [, $linked] = $this->makeLinkedPair(linkedVerified: false);
        $this->signInWithProvider($linked);
        $code = $this->sentCode();

        // Someone else's browser: a fresh session that never started this sign-in.
        $this->flushSession();
        $this->post(route('auth.confirm-sign-in.check'), ['code' => $code])->assertRedirect(route('login'));

        $this->assertGuest();
        $this->assertNull($linked->refresh()->email_verified_at);
    }

    public function test_an_expired_code_is_refused()
    {
        [, $linked] = $this->makeLinkedPair(linkedVerified: false);
        $this->signInWithProvider($linked);
        $code = $this->sentCode();

        $this->travel(SignInChallenge::LIFETIME_MINUTES + 1)->minutes();

        $this->post(route('auth.confirm-sign-in.check'), ['code' => $code])->assertRedirect(route('login'));
        $this->assertGuest();
    }
}
