<?php

namespace Tests\Unit\Controllers;

use App\Events\AccountSecurityActivity;
use App\Events\AccountSecurityActivityResultEnum;
use App\Http\Controllers\Api\v3\PasskeyApiController;
use App\Models\Account;
use App\Models\WebAuthnCredential;
use App\Security\WebAuthnService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Mockery;
use Tests\TestCase;

class PasskeyAuthenticationControllerTest extends TestCase
{
    use DatabaseTransactions;
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    private function createController(?WebAuthnService $webAuthnService = null): PasskeyApiController
    {
        $webAuthnService = $webAuthnService ?? Mockery::mock(WebAuthnService::class);

        return new PasskeyApiController($webAuthnService);
    }

    public function test_generate_registration_challenge_requires_authentication()
    {
        $controller = $this->createController();
        $request = Request::create('/api/v3/passkey/register/challenge', 'POST', [
            'display_name' => 'My Passkey',
        ]);

        $response = $controller->generateRegistrationChallenge($request);

        $this->assertEquals(401, $response->getStatusCode());
        $this->assertJson($response->getContent());
        $data = json_decode($response->getContent(), true);
        $this->assertEquals('Unauthenticated', $data['error']);
    }

    public function test_generate_registration_challenge_success()
    {
        Event::fake();

        $account = Account::factory()->create();
        Auth::login($account);

        $webAuthnService = Mockery::mock(WebAuthnService::class);
        $challengeData = [
            'challenge' => base64_encode(random_bytes(32)),
            'session_id' => 'test-session-id',
            'user' => ['id' => '1', 'name' => $account->email],
            'rp' => ['name' => 'Test', 'id' => 'example.com'],
        ];

        $webAuthnService
            ->shouldReceive('generateRegistrationChallenge')
            ->with($account, 'My Passkey')
            ->once()
            ->andReturn($challengeData);

        $controller = $this->createController($webAuthnService);

        $request = Request::create('/api/v3/passkey/register/challenge', 'POST', [
            'display_name' => 'My Passkey',
        ]);
        $request->setUserResolver(fn () => $account);

        $response = $controller->generateRegistrationChallenge($request);

        $this->assertEquals(200, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertEquals($challengeData, $data);
    }

    public function test_generate_registration_challenge_validates_display_name()
    {
        $account = Account::factory()->create();
        Auth::login($account);

        $controller = $this->createController();

        $request = Request::create('/api/v3/passkey/register/challenge', 'POST', [
            'display_name' => '',
        ]);
        $request->setUserResolver(fn () => $account);

        $this->expectException(\Illuminate\Validation\ValidationException::class);

        $controller->generateRegistrationChallenge($request);
    }

    public function test_generate_authentication_challenge_success()
    {
        $webAuthnService = Mockery::mock(WebAuthnService::class);
        $challengeData = [
            'challenge' => base64_encode(random_bytes(32)),
            'session_id' => 'test-session-id',
            'allowCredentials' => [],
            'userVerification' => 'preferred',
            'timeout' => 60000,
        ];

        $webAuthnService
            ->shouldReceive('generateAuthenticationChallenge')
            ->with('test@example.com')
            ->once()
            ->andReturn($challengeData);

        $controller = $this->createController($webAuthnService);

        $request = Request::create('/api/v3/passkey/login/challenge', 'POST', [
            'email' => 'test@example.com',
        ]);

        $response = $controller->generateAuthenticationChallenge($request);

        $this->assertEquals(200, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertEquals($challengeData, $data);
    }

    public function test_generate_authentication_challenge_validates_email()
    {
        $controller = $this->createController();

        $request = Request::create('/api/v3/passkey/login/challenge', 'POST', [
            'email' => 'invalid-email',
        ]);

        $this->expectException(\Illuminate\Validation\ValidationException::class);

        $controller->generateAuthenticationChallenge($request);
    }

    public function test_verify_registration_response_success()
    {
        Event::fake();

        $account = Account::factory()->create();
        Auth::login($account);

        // Create a real credential instance for testing
        $credential = new WebAuthnCredential();
        $credential->id = 1;
        $credential->display_name = 'My Passkey';
        $credential->created_at = Carbon::now();
        $credential->last_used_at = null;
        $credential->transport = 'usb';

        $webAuthnService = Mockery::mock(WebAuthnService::class);
        $webAuthnService
            ->shouldReceive('verifyAndStoreCredential')
            ->with(
                $account,
                'client-data-json',
                'attestation-object',
                'session-id',
                Mockery::any() // transports parameter (optional)
            )
            ->once()
            ->andReturn($credential);

        $controller = $this->createController($webAuthnService);

        $request = Request::create('/api/v3/passkey/register/verify', 'POST', [
            'session_id' => 'session-id',
            'client_data_json' => 'client-data-json',
            'attestation_object' => 'attestation-object',
        ]);
        $request->setUserResolver(fn () => $account);

        $response = $controller->verifyRegistrationResponse($request);

        $this->assertEquals(200, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertTrue($data['success']);
        $this->assertArrayHasKey('credential', $data);

        Event::assertDispatched(AccountSecurityActivity::class, function ($event) use ($account) {
            return $event->account->id === $account->id &&
                   $event->type === 'passkey_register' &&
                   $event->result === AccountSecurityActivityResultEnum::SUCCESS;
        });
    }

    public function test_verify_registration_response_failure_logs_event()
    {
        Event::fake();

        $account = Account::factory()->create();
        Auth::login($account);

        $webAuthnService = Mockery::mock(WebAuthnService::class);
        $webAuthnService
            ->shouldReceive('verifyAndStoreCredential')
            ->andThrow(new \App\Exceptions\WebAuthnException('Verification failed'));

        $controller = $this->createController($webAuthnService);

        $request = Request::create('/api/v3/passkey/register/verify', 'POST', [
            'session_id' => 'session-id',
            'client_data_json' => 'client-data-json',
            'attestation_object' => 'attestation-object',
        ]);
        $request->setUserResolver(fn () => $account);

        $response = $controller->verifyRegistrationResponse($request);

        $this->assertEquals(400, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertEquals('Verification failed', $data['error']);

        Event::assertDispatched(AccountSecurityActivity::class, function ($event) use ($account) {
            return $event->account->id === $account->id &&
                   $event->type === 'passkey_register' &&
                   $event->result === AccountSecurityActivityResultEnum::FAILURE;
        });
    }

    public function test_verify_authentication_response_success()
    {
        Event::fake();

        $account = Account::factory()->create([
            'is_master_account' => true,
        ]);

        $webAuthnService = Mockery::mock(WebAuthnService::class);
        $webAuthnService
            ->shouldReceive('verifyAuthenticationResponse')
            ->with(
                'test@example.com',
                'client-data-json',
                'authenticator-assertion-object',
                'session-id'
            )
            ->once()
            ->andReturn($account);

        Auth::shouldReceive('login')
            ->with($account, false)
            ->once();

        $controller = $this->createController($webAuthnService);

        $request = Request::create('/api/v3/passkey/login/verify', 'POST', [
            'email' => 'test@example.com',
            'session_id' => 'session-id',
            'client_data_json' => 'client-data-json',
            'authenticator_assertion_object' => 'authenticator-assertion-object',
        ]);

        $response = $controller->verifyAuthenticationResponse($request);

        $this->assertEquals(200, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertTrue($data['success']);
        $this->assertArrayHasKey('account', $data);

        Event::assertDispatched(AccountSecurityActivity::class, function ($event) use ($account) {
            return $event->account->id === $account->id &&
                   $event->type === 'passkey_auth' &&
                   $event->result === AccountSecurityActivityResultEnum::SUCCESS;
        });
    }

    public function test_verify_authentication_response_failure_logs_event()
    {
        Event::fake();

        // Create account first so it can be found for event logging
        $account = Account::factory()->create([
            'email' => 'test@example.com',
            'is_master_account' => true,
        ]);

        $webAuthnService = Mockery::mock(WebAuthnService::class);
        $webAuthnService
            ->shouldReceive('verifyAuthenticationResponse')
            ->andThrow(new \App\Exceptions\WebAuthnException('Authentication failed'));

        $controller = $this->createController($webAuthnService);

        $request = Request::create('/api/v3/passkey/login/verify', 'POST', [
            'email' => 'test@example.com',
            'session_id' => 'session-id',
            'client_data_json' => 'client-data-json',
            'authenticator_assertion_object' => 'authenticator-assertion-object',
        ]);

        $response = $controller->verifyAuthenticationResponse($request);

        $this->assertEquals(401, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertEquals('Authentication failed', $data['error']);

        // The controller looks up the account for event logging, so it should be dispatched
        Event::assertDispatched(AccountSecurityActivity::class, function ($event) use ($account) {
            return $event->account->id === $account->id &&
                   $event->type === 'passkey_auth' &&
                   $event->result === AccountSecurityActivityResultEnum::FAILURE;
        });
    }

    public function test_get_passkeys_returns_user_passkeys()
    {
        $account = Account::factory()->create();
        $credential1 = WebAuthnCredential::factory()->create([
            'account_id' => $account->id,
            'display_name' => 'Passkey 1',
        ]);
        $credential2 = WebAuthnCredential::factory()->create([
            'account_id' => $account->id,
            'display_name' => 'Passkey 2',
        ]);

        $webAuthnService = Mockery::mock(WebAuthnService::class);
        $webAuthnService
            ->shouldReceive('getCredentialsForAccount')
            ->with($account)
            ->once()
            ->andReturn(collect([$credential1, $credential2]));

        $controller = $this->createController($webAuthnService);

        $request = Request::create('/api/v3/passkey', 'GET');
        $request->setUserResolver(fn () => $account);

        $response = $controller->getPasskeys($request);

        $this->assertEquals(200, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('passkeys', $data);
        $this->assertCount(2, $data['passkeys']);
    }

    public function test_delete_passkey_success()
    {
        Event::fake();

        $account = Account::factory()->create([
            'password' => \Illuminate\Support\Facades\Hash::make('password123'),
            'is_passworded' => true,
        ]);

        $credential = WebAuthnCredential::factory()->create([
            'account_id' => $account->id,
        ]);

        $webAuthnService = app(WebAuthnService::class);
        $controller = $this->createController($webAuthnService);

        $request = Request::create("/api/v3/passkey/{$credential->id}", 'DELETE', [
            'password' => 'password123',
        ]);
        $request->setUserResolver(fn () => $account);

        $response = $controller->deletePasskey($request, $credential->id);

        $this->assertEquals(200, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertTrue($data['success']);

        Event::assertDispatched(AccountSecurityActivity::class, function ($event) use ($account) {
            return $event->account->id === $account->id &&
                   $event->type === 'passkey_delete' &&
                   $event->result === AccountSecurityActivityResultEnum::SUCCESS;
        });
    }

    public function test_delete_passkey_requires_password_when_last()
    {
        $account = Account::factory()->create([
            'password' => null,
            'is_passworded' => false,
        ]);

        $credential = WebAuthnCredential::factory()->create([
            'account_id' => $account->id,
        ]);

        $webAuthnService = app(WebAuthnService::class);
        $controller = $this->createController($webAuthnService);

        $request = Request::create("/api/v3/passkey/{$credential->id}", 'DELETE');
        $request->setUserResolver(fn () => $account);

        $response = $controller->deletePasskey($request, $credential->id);

        $this->assertEquals(422, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertStringContainsString('password', strtolower($data['error'] ?? ''));
    }

}

