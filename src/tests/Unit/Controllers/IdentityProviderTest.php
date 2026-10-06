<?php

namespace Tests\Unit\Controllers;

use App\Models\Account;
use App\Models\AuthorizationProvider;
use App\Security\Identity\GoogleIdentityProvider;
use App\Security\Identity\IdentityProviderRegistry;
use App\Security\Identity\TestIdentityProvider;
use App\Security\RoleConstants;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use Mockery;
use Tests\TestCase;

class IdentityProviderTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
    }

    private function makeProvider(string $className): AuthorizationProvider
    {
        $nameIdentifier = 'idp-'.Str::lower(Str::random(8));
        config(['ed.identity_providers.'.$nameIdentifier => $className]);

        return AuthorizationProvider::create([
            'name' => "Provider $nameIdentifier",
            'name_identifier' => $nameIdentifier,
            'logo_file_name' => "$nameIdentifier.png",
        ]);
    }

    /**
     * Makes the Socialite driver for `$provider` return this person, with Google's raw claims.
     */
    private function fakeSocialiteUser(AuthorizationProvider $provider, string $subject, string $email, array $raw = [])
    {
        $user = (new SocialiteUser)->setRaw($raw)->map(['id' => $subject, 'email' => $email, 'name' => 'Someone']);
        $driver = Mockery::mock();
        $driver->shouldReceive('user')->andReturn($user);
        Socialite::shouldReceive('driver')->with($provider->name_identifier)->andReturn($driver);
    }

    private function signIn(AuthorizationProvider $provider, array $query = [])
    {
        return $this->get('/federated-auth/callback/'.$provider->name_identifier.($query ? '?'.http_build_query($query) : ''));
    }

    private function useLocalEnvironment(): void
    {
        $this->app['env'] = 'local';
        config([
            'ed.identity_providers.test' => TestIdentityProvider::class,
            'ed.recaptcha.sitekey' => null,
        ]);
    }

    // -------------------------------------------------------------------------
    // The registry
    // -------------------------------------------------------------------------

    public function test_the_registry_hands_out_only_registered_and_available_providers()
    {
        config(['ed.identity_providers.test' => TestIdentityProvider::class]);
        $registry = resolve(IdentityProviderRegistry::class);

        $this->assertInstanceOf(GoogleIdentityProvider::class, $registry->find('google'));
        $this->assertNull($registry->find('facebook'), 'retired by leaving it out of the configuration');
        $this->assertNull($registry->find('test'), 'unavailable outside APP_ENV=local, whatever the configuration says');
        $this->assertNotContains('test', $registry->availableNameIdentifiers());
        $this->assertContains('google', $registry->availableNameIdentifiers());
    }

    public function test_an_unavailable_provider_is_refused_on_the_callback()
    {
        config(['ed.identity_providers.test' => TestIdentityProvider::class]);
        $provider = AuthorizationProvider::firstOrCreate(['name_identifier' => 'test'], ['name' => 'Test', 'logo_file_name' => 'test.svg']);

        $this->signIn($provider, ['email' => 'intruder@example.com', 'verified' => 1])->assertRedirect();

        $this->assertGuest();
        $this->assertFalse(Account::where('email', 'intruder@example.com')->exists());
    }

    public function test_the_login_page_offers_only_available_providers()
    {
        $hidden = AuthorizationProvider::create(['name' => 'Unregistered', 'name_identifier' => 'unregistered', 'logo_file_name' => 'x.png']);

        $this->get(route('login'))
            ->assertOk()
            ->assertSee(route('auth.redirect', ['providerName' => 'google']))
            ->assertDontSee(route('auth.redirect', ['providerName' => $hidden->name_identifier]));
    }

    // -------------------------------------------------------------------------
    // Google's `email_verified`
    // -------------------------------------------------------------------------

    public function test_google_vouches_only_for_a_strictly_true_email_verified_claim()
    {
        $provider = $this->makeProvider(GoogleIdentityProvider::class);
        $google = resolve(IdentityProviderRegistry::class)->find($provider->name_identifier);

        $cases = [[true, true], [false, false], ['true', false], [null, false]];
        $users = array_map(fn (array $case) => (new SocialiteUser)
            ->setRaw($case[0] === null ? [] : ['email_verified' => $case[0]])
            ->map(['id' => 'g-1', 'email' => 'g@example.com']), $cases);
        $driver = Mockery::mock();
        $driver->shouldReceive('user')->andReturn(...$users);
        Socialite::shouldReceive('driver')->with($provider->name_identifier)->andReturn($driver);

        foreach ($cases as [$claim, $expected]) {
            $this->assertSame($expected, $google->resolveIdentity(Request::create('/'))->emailVerified, var_export($claim, true));
        }
    }

    public function test_a_vouched_address_registers_a_verified_account()
    {
        $provider = $this->makeProvider(GoogleIdentityProvider::class);
        $this->fakeSocialiteUser($provider, 'g-new', 'vouched@example.com', ['email_verified' => true]);

        $this->signIn($provider)->assertRedirect();

        $this->assertNotNull(Account::where('identity', 'g-new')->first()->email_verified_at);
    }

    public function test_an_unvouched_address_registers_an_unverified_account()
    {
        $provider = $this->makeProvider(GoogleIdentityProvider::class);
        $this->fakeSocialiteUser($provider, 'g-unvouched', 'unvouched@example.com', ['email_verified' => false]);

        $this->signIn($provider)->assertRedirect();

        $this->assertNull(Account::where('identity', 'g-unvouched')->first()->email_verified_at);
    }

    public function test_a_vouched_address_spares_a_linked_account_the_e_mailed_code()
    {
        $provider = $this->makeProvider(GoogleIdentityProvider::class);
        [$master, $linked] = $this->makeLinkedPair($provider, 'linked@example.com');
        $this->fakeSocialiteUser($provider, $linked->identity, 'linked@example.com', ['email_verified' => true]);

        $this->signIn($provider)->assertRedirect();

        $this->assertAuthenticatedAs($master);
        $this->assertNotNull($linked->refresh()->email_verified_at);
    }

    public function test_a_vouch_for_a_different_address_proves_nothing_about_ours()
    {
        $provider = $this->makeProvider(GoogleIdentityProvider::class);
        [, $linked] = $this->makeLinkedPair($provider, 'ours@example.com');
        $this->fakeSocialiteUser($provider, $linked->identity, 'changed-at-google@example.com', ['email_verified' => true]);

        $this->signIn($provider)->assertRedirect(route('auth.confirm-sign-in'));

        $this->assertGuest();
        $this->assertNull($linked->refresh()->email_verified_at);
    }

    // -------------------------------------------------------------------------
    // Pre-2017 accounts, whose identity is a hash of the subject
    // -------------------------------------------------------------------------

    public function test_a_legacy_account_is_claimed_by_the_subject_its_hash_proves()
    {
        $provider = $this->makeProvider(GoogleIdentityProvider::class);
        $legacy = $this->makeLegacyAccount($provider, 'g-legacy', 'legacy@example.com');
        $this->fakeSocialiteUser($provider, 'g-legacy', 'legacy@example.com', ['email_verified' => true]);

        $this->signIn($provider)->assertRedirect();

        $this->assertAuthenticatedAs($legacy);
        $this->assertSame('g-legacy', $legacy->refresh()->identity);
        $this->assertSame(1, Account::where('email', 'legacy@example.com')->count());
    }

    public function test_a_legacy_account_is_not_claimed_by_another_subject_on_its_address()
    {
        $provider = $this->makeProvider(GoogleIdentityProvider::class);
        $legacy = $this->makeLegacyAccount($provider, 'g-legacy', 'legacy@example.com');
        $hash = $legacy->identity;
        $this->fakeSocialiteUser($provider, 'g-impostor', 'legacy@example.com', ['email_verified' => true]);

        $this->signIn($provider)->assertRedirect();

        $this->assertSame($hash, $legacy->refresh()->identity);
        $this->assertNotSame($legacy->id, auth()->id());
    }

    private function makeLegacyAccount(AuthorizationProvider $provider, string $subject, string $email): Account
    {
        /** @var Account */
        $account = Account::factory()->createOne([
            'email' => $email,
            'authorization_provider_id' => $provider->id,
            'identity' => password_hash($provider->id.'_'.$subject, PASSWORD_BCRYPT, ['cost' => 4]),
            'email_verified_at' => null,
        ]);
        $account->addMembershipTo(RoleConstants::Users);

        return $account;
    }

    // -------------------------------------------------------------------------
    // The local test provider
    // -------------------------------------------------------------------------

    public function test_the_test_provider_signs_in_as_anyone_locally()
    {
        $this->useLocalEnvironment();
        $provider = AuthorizationProvider::firstOrCreate(['name_identifier' => 'test'], ['name' => 'Test', 'logo_file_name' => 'test.svg']);

        $this->get(route('auth.redirect', ['providerName' => 'test']))->assertOk()->assertSee('Sign in as anyone');

        $this->signIn($provider, ['email' => 'tester@example.com', 'name' => 'Tester', 'verified' => 1])->assertRedirect();
        $first = Account::where('identity', 'test|tester@example.com')->firstOrFail();
        $this->assertAuthenticatedAs($first);
        $this->assertNotNull($first->email_verified_at);

        // The same address signs back in; another identity is a second account on it.
        auth()->logout();
        $this->signIn($provider, ['email' => 'tester@example.com']);
        $this->assertAuthenticatedAs($first);

        auth()->logout();
        $this->signIn($provider, ['email' => 'tester@example.com', 'identity' => 'second']);
        $second = Account::where('identity', 'test|second')->firstOrFail();
        $this->assertSame('tester@example.com', $second->email);
        $this->assertNull($second->email_verified_at);
    }

    public function test_the_command_refuses_outside_local()
    {
        $before = AuthorizationProvider::withTrashed()->where('name_identifier', 'test')->first()?->toArray();

        $this->artisan('ed:test-identity-provider')->assertFailed();

        $this->assertSame($before, AuthorizationProvider::withTrashed()->where('name_identifier', 'test')->first()?->toArray());
    }

    /**
     * @return array{0: Account, 1: Account}
     */
    private function makeLinkedPair(AuthorizationProvider $provider, string $email): array
    {
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
            'authorization_provider_id' => $provider->id,
            'identity' => 'subject-'.Str::random(12),
            'master_account_id' => $master->id,
            'email_verified_at' => null,
        ]);
        $linked->addMembershipTo(RoleConstants::Users);

        return [$master, $linked];
    }
}
