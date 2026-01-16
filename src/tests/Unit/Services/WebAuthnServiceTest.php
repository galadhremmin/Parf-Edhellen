<?php

namespace Tests\Unit\Services;

use App\Exceptions\WebAuthnException;
use App\Models\Account;
use App\Models\WebAuthnCredential;
use App\Models\WebAuthnSession;
use App\Security\WebAuthnService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Mockery;
use Tests\TestCase;

class WebAuthnServiceTest extends TestCase
{
    use DatabaseTransactions;
    private WebAuthnService $service;

    protected function setUp(): void
    {
        parent::setUp();

        // Set up WebAuthn config for testing
        Config::set('webauthn.rp.id', 'example.com');
        Config::set('webauthn.rp.name', 'Test App');
        Config::set('webauthn.rp.origin', 'https://example.com');
        Config::set('webauthn.challenge.length', 32);
        Config::set('webauthn.challenge.timeout', 60000);
        Config::set('webauthn.challenge.session_ttl', 600);
        Config::set('webauthn.attestation.conveyance', 'none');
        Config::set('webauthn.authenticator.user_verification', 'preferred');
        Config::set('webauthn.authenticator.resident_key', 'preferred');

        $this->service = new WebAuthnService();
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_generate_registration_challenge_creates_session()
    {
        $account = Account::factory()->create([
            'email' => 'test@example.com',
            'nickname' => 'Test User',
        ]);

        $challenge = $this->service->generateRegistrationChallenge($account, 'My Passkey');

        $this->assertArrayHasKey('challenge', $challenge);
        $this->assertArrayHasKey('session_id', $challenge);
        $this->assertArrayHasKey('user', $challenge);
        $this->assertArrayHasKey('rp', $challenge);
        // displayName in the challenge response is the account's nickname, not the passkey display name
        $this->assertEquals('Test User', $challenge['user']['displayName'] ?? null);

        // Verify session was created
        $session = WebAuthnSession::where('account_id', $account->id)
            ->where('session_type', 'registration')
            ->first();

        $this->assertNotNull($session);
        $this->assertEquals($challenge['challenge'], $session->challenge);
        $this->assertEquals('My Passkey', $session->challenge_data['display_name'] ?? null);
    }

    public function test_generate_registration_challenge_validates_display_name()
    {
        $account = Account::factory()->create();

        $this->expectException(WebAuthnException::class);
        $this->expectExceptionMessage('Display name must be between 1 and 255 characters');

        $this->service->generateRegistrationChallenge($account, '');
    }

    public function test_generate_registration_challenge_validates_display_name_length()
    {
        $account = Account::factory()->create();

        $this->expectException(WebAuthnException::class);
        $this->expectExceptionMessage('Display name must be between 1 and 255 characters');

        $this->service->generateRegistrationChallenge($account, str_repeat('a', 256));
    }

    public function test_generate_authentication_challenge_creates_session()
    {
        $account = Account::factory()->create([
            'email' => 'test@example.com',
            'is_master_account' => true,
        ]);

        // Create a credential for the account
        WebAuthnCredential::factory()->create([
            'account_id' => $account->id,
            'is_active' => true,
        ]);

        $challenge = $this->service->generateAuthenticationChallenge('test@example.com');

        $this->assertArrayHasKey('challenge', $challenge);
        $this->assertArrayHasKey('session_id', $challenge);
        $this->assertArrayHasKey('allowCredentials', $challenge);
        $this->assertCount(1, $challenge['allowCredentials']);

        // Verify session was created
        $session = WebAuthnSession::where('email', 'test@example.com')
            ->where('session_type', 'authentication')
            ->first();

        $this->assertNotNull($session);
    }

    public function test_generate_authentication_challenge_returns_empty_credentials_for_nonexistent_account()
    {
        // Don't create account - test privacy behavior
        $challenge = $this->service->generateAuthenticationChallenge('nonexistent@example.com');

        $this->assertArrayHasKey('challenge', $challenge);
        $this->assertArrayHasKey('allowCredentials', $challenge);
        $this->assertCount(0, $challenge['allowCredentials']);

        // Session should still be created for timing attack prevention
        $session = WebAuthnSession::where('email', 'nonexistent@example.com')
            ->where('session_type', 'authentication')
            ->first();

        $this->assertNotNull($session);
    }

    public function test_delete_credential_success()
    {
        $account = Account::factory()->create([
            'is_passworded' => true,
        ]);

        $credential = WebAuthnCredential::factory()->create([
            'account_id' => $account->id,
            'is_active' => true,
        ]);

        // Create another credential so deletion is allowed
        WebAuthnCredential::factory()->create([
            'account_id' => $account->id,
            'is_active' => true,
        ]);

        $this->service->deleteCredential($credential, $account);

        $this->assertDatabaseMissing('webauthn_credentials', [
            'id' => $credential->id,
        ]);
    }

    public function test_delete_credential_prevents_deleting_last_without_password()
    {
        $account = Account::factory()->create([
            'is_passworded' => false,
        ]);

        $credential = WebAuthnCredential::factory()->create([
            'account_id' => $account->id,
            'is_active' => true,
        ]);

        $this->expectException(WebAuthnException::class);
        $this->expectExceptionMessage('Cannot delete the only passkey without a password. Add a password first or contact support.');

        $this->service->deleteCredential($credential, $account);
    }

    public function test_delete_credential_allows_deleting_last_with_password()
    {
        $account = Account::factory()->create([
            'is_passworded' => true,
        ]);

        $credential = WebAuthnCredential::factory()->create([
            'account_id' => $account->id,
            'is_active' => true,
        ]);

        $this->service->deleteCredential($credential, $account);

        $this->assertDatabaseMissing('webauthn_credentials', [
            'id' => $credential->id,
        ]);

        // Verify has_passkeys flag is updated
        $account->refresh();
        // has_passkeys might be 0 (integer) from database, so check for falsy value
        $this->assertFalse((bool) $account->has_passkeys);
    }

    public function test_delete_credential_unauthorized()
    {
        $account1 = Account::factory()->create();
        $account2 = Account::factory()->create();

        $credential = WebAuthnCredential::factory()->create([
            'account_id' => $account1->id,
        ]);

        $this->expectException(WebAuthnException::class);
        $this->expectExceptionMessage('Unauthorized to delete this credential');

        $this->service->deleteCredential($credential, $account2);
    }


    public function test_get_credentials_for_account()
    {
        $account = Account::factory()->create();

        $activeCredential = WebAuthnCredential::factory()->create([
            'account_id' => $account->id,
            'is_active' => true,
        ]);

        $inactiveCredential = WebAuthnCredential::factory()->create([
            'account_id' => $account->id,
            'is_active' => false,
        ]);

        $credentials = $this->service->getCredentialsForAccount($account);

        $this->assertCount(1, $credentials);
        $this->assertEquals($activeCredential->id, $credentials->first()->id);
    }

    public function test_cleanup_expired_sessions()
    {
        // Clean up any existing sessions first (use delete for SQLite compatibility)
        DB::table('webauthn_sessions')->delete();

        // Create expired session
        WebAuthnSession::factory()->create([
            'expires_at' => Carbon::now()->subHour(),
        ]);

        // Create valid session
        WebAuthnSession::factory()->create([
            'expires_at' => Carbon::now()->addHour(),
        ]);

        $deleted = $this->service->cleanupExpiredSessions();

        $this->assertEquals(1, $deleted);
        $this->assertDatabaseCount('webauthn_sessions', 1);
    }
}

