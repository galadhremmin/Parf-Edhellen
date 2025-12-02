<?php

namespace Tests\Unit\Controllers;

use App\Events\AccountPasswordForgot;
use App\Events\AccountSecurityActivity;
use App\Events\AccountSecurityActivityResultEnum;
use App\Exceptions\SuspiciousBotActivityException;
use App\Helpers\RecaptchaHelper;
use App\Http\Controllers\Authentication\UsernamePasswordAuthenticationController;
use App\Models\Account;
use App\Models\AuthorizationProvider;
use App\Security\AccountManager;
use App\Security\RoleConstants;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password as FacadesPassword;
use Illuminate\Validation\ValidationException;
use Mockery;
use Tests\TestCase;

class UsernamePasswordAuthenticationControllerTest extends TestCase
{
    // use RefreshDatabase; // Commented out to avoid database setup issues

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    private function createController(AccountManager $accountManager = null): UsernamePasswordAuthenticationController
    {
        $accountManager = $accountManager ?? Mockery::mock(AccountManager::class);

        // Mock SystemErrorRepository to avoid database writes
        $systemErrorRepo = Mockery::mock(\App\Repositories\SystemErrorRepository::class);
        $systemErrorRepo->shouldReceive('saveException')->andReturn(Mockery::mock(\App\Models\SystemError::class));

        $controller = Mockery::mock(UsernamePasswordAuthenticationController::class, [
            $systemErrorRepo,
            $accountManager
        ])->makePartial()->shouldAllowMockingProtectedMethods();

        // Mock the doLogin method to avoid session dependencies
        $controller->shouldReceive('doLogin')->andReturn(redirect('/'));

        return $controller;
    }

    public function test_forgot_password_returns_view()
    {
        $controller = $this->createController();
        $request = Request::create('/forgot-password', 'GET', ['status' => 'sent']);

        $response = $controller->forgotPassword($request);

        $this->assertInstanceOf(\Illuminate\View\View::class, $response);
        $this->assertEquals('authentication.forgot-password', $response->name());
        $this->assertEquals('sent', $response->getData()['status']);
    }

    public function test_login_with_password_successful()
    {
        Event::fake();

        // Create partial mock account to avoid database operations while allowing property access
        $account = Mockery::mock(Account::class)->makePartial();
        $account->id = 1;
        $account->email = 'test@example.com';
        $account->master_account = null;
        $account->shouldReceive('memberOf')->with(RoleConstants::Users)->andReturn(true);
        $account->shouldReceive('save')->andReturn(true);

        $accountManager = Mockery::mock(AccountManager::class);
        $accountManager
            ->shouldReceive('checkPasswordWithUsername')
            ->with('test@example.com', 'password123')
            ->andReturn(true);
        $accountManager
            ->shouldReceive('getAccountByUsername')
            ->with('test@example.com')
            ->andReturn($account);

        $controller = $this->createController($accountManager);

        // Mock RecaptchaHelper static method
        $assessmentResult = ['tokenProperties' => ['valid' => true], 'score' => 0.9];
        Mockery::mock('alias:App\Helpers\RecaptchaHelper')
            ->shouldReceive('createAssessment')
            ->with('recaptcha-token', 'LOGIN', Mockery::on(function (&$result) use ($assessmentResult) {
                $result = $assessmentResult;
                return true;
            }))
            ->andReturn(true);

        $request = Request::create('/login', 'POST', [
            'username' => 'test@example.com',
            'password' => 'password123',
            'recaptcha_token' => 'recaptcha-token',
            'remember' => true,
        ]);

        $response = $controller->loginWithPassword($request);

        $this->assertEquals(302, $response->getStatusCode());
        Event::assertDispatched(AccountSecurityActivity::class, function ($event) use ($account, $assessmentResult) {
            return $event->account->id === $account->id &&
                   $event->type === 'login' &&
                   $event->result === AccountSecurityActivityResultEnum::SUCCESS;
        });
    }

    public function test_login_with_password_wrong_credentials()
    {
        Event::fake();

        // Create partial mock account to avoid database operations
        $account = Mockery::mock(Account::class)->makePartial();
        $account->id = 1;
        $account->email = 'test@example.com';

        $accountManager = Mockery::mock(AccountManager::class);
        $accountManager
            ->shouldReceive('checkPasswordWithUsername')
            ->with('test@example.com', 'wrongpassword')
            ->andReturn(false);
        $accountManager
            ->shouldReceive('getAccountByUsername')
            ->with('test@example.com')
            ->andReturn($account);

        $controller = $this->createController($accountManager);

        $request = Request::create('/login', 'POST', [
            'username' => 'test@example.com',
            'password' => 'wrongpassword',
            'recaptcha_token' => 'recaptcha-token',
        ]);

        $this->expectException(ValidationException::class);

        try {
            $controller->loginWithPassword($request);
        } catch (ValidationException $e) {
            Event::assertDispatched(AccountSecurityActivity::class, function ($event) use ($account) {
                return $event->account->id === $account->id &&
                       $event->type === 'login' &&
                       $event->result === AccountSecurityActivityResultEnum::FAILURE;
            });
            throw $e;
        }
    }

    public function test_login_with_password_recaptcha_failure()
    {
        Event::fake();

        // Create partial mock account to avoid database operations
        $account = Mockery::mock(Account::class)->makePartial();
        $account->id = 1;
        $account->email = 'test@example.com';

        $accountManager = Mockery::mock(AccountManager::class);
        $accountManager
            ->shouldReceive('checkPasswordWithUsername')
            ->with('test@example.com', 'password123')
            ->andReturn(true);
        $accountManager
            ->shouldReceive('getAccountByUsername')
            ->with('test@example.com')
            ->andReturn($account);

        $controller = $this->createController($accountManager);

        // Mock RecaptchaHelper failure
        $assessmentResult = ['tokenProperties' => ['valid' => false], 'error' => 'invalid-token'];
        Mockery::mock('alias:App\Helpers\RecaptchaHelper')
            ->shouldReceive('createAssessment')
            ->with('invalid-token', 'LOGIN', Mockery::on(function (&$result) use ($assessmentResult) {
                $result = $assessmentResult;
                return true;
            }))
            ->andReturn(false);

        $request = Request::create('/login', 'POST', [
            'username' => 'test@example.com',
            'password' => 'password123',
            'recaptcha_token' => 'invalid-token',
        ]);

        $this->expectException(ValidationException::class);

        try {
            $controller->loginWithPassword($request);
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('recaptcha', $e->errors());
            Event::assertDispatched(AccountSecurityActivity::class, function ($event) use ($account) {
                return $event->account->id === $account->id &&
                       $event->type === 'login' &&
                       $event->result === AccountSecurityActivityResultEnum::BLOCKED;
            });
            throw $e;
        }
    }

    public function test_register_with_password_successful()
    {
        Event::fake();

        // Create partial mock account to avoid database operations
        $account = Mockery::mock(Account::class)->makePartial();
        $account->id = 1;
        $account->email = 'newuser@example.com';
        $account->master_account = null;
        $account->shouldReceive('memberOf')->with(RoleConstants::Users)->andReturn(true);
        $account->shouldReceive('save')->andReturn(true);

        $accountManager = Mockery::mock(AccountManager::class);
        $accountManager
            ->shouldReceive('getAccountByUsername')
            ->with('newuser@example.com')
            ->andReturn(null);
        $accountManager
            ->shouldReceive('createAccount')
            ->with('newuser@example.com', null, null, 'password123', 'testuser')
            ->andReturn($account);

        $controller = $this->createController($accountManager);

        // Mock RecaptchaHelper
        $assessmentResult = ['tokenProperties' => ['valid' => true], 'score' => 0.8];
        Mockery::mock('alias:App\Helpers\RecaptchaHelper')
            ->shouldReceive('createAssessment')
            ->with('recaptcha-token', 'REGISTER', Mockery::on(function (&$result) use ($assessmentResult) {
                $result = $assessmentResult;
                return true;
            }))
            ->andReturn(true);

        $request = Request::create('/register', 'POST', [
            'username' => 'newuser@example.com',
            'nickname' => 'testuser',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'recaptcha_token' => 'recaptcha-token',
        ]);

        $response = $controller->registerWithPassword($request);

        $this->assertEquals(302, $response->getStatusCode());
        Event::assertDispatched(AccountSecurityActivity::class, function ($event) {
            return $event->type === 'registration' &&
                   $event->result === AccountSecurityActivityResultEnum::SUCCESS;
        });
    }

    public function test_register_with_password_recaptcha_failure()
    {
        // Mock RecaptchaHelper failure
        $assessmentResult = ['tokenProperties' => ['valid' => false], 'error' => 'invalid-token'];
        Mockery::mock('alias:App\Helpers\RecaptchaHelper')
            ->shouldReceive('createAssessment')
            ->with('invalid-token', 'REGISTER', Mockery::on(function (&$result) use ($assessmentResult) {
                $result = $assessmentResult;
                return true;
            }))
            ->andReturn(false);

        $request = Request::create('/register', 'POST', [
            'username' => 'newuser@example.com',
            'nickname' => 'testuser',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'recaptcha_token' => 'invalid-token',
        ]);

        $controller = $this->createController();
        $response = $controller->registerWithPassword($request);

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals(route('login'), $response->getTargetUrl());
    }

    public function test_register_with_password_existing_username()
    {
        // Create partial mock account to avoid database operations
        $existingAccount = Mockery::mock(Account::class)->makePartial();
        $existingAccount->id = 1;
        $existingAccount->email = 'existing@example.com';

        $accountManager = Mockery::mock(AccountManager::class);
        $accountManager
            ->shouldReceive('getAccountByUsername')
            ->with('existing@example.com')
            ->andReturn($existingAccount);

        $controller = $this->createController($accountManager);

        // Mock RecaptchaHelper to pass validation
        $assessmentResult = ['tokenProperties' => ['valid' => true], 'score' => 0.8];
        Mockery::mock('alias:App\Helpers\RecaptchaHelper')
            ->shouldReceive('createAssessment')
            ->with('recaptcha-token', 'REGISTER', Mockery::on(function (&$result) use ($assessmentResult) {
                $result = $assessmentResult;
                return true;
            }))
            ->andReturn(true);

        $request = Request::create('/register', 'POST', [
            'username' => 'existing@example.com',
            'nickname' => 'testuser',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'recaptcha_token' => 'recaptcha-token',
        ]);

        $this->expectException(ValidationException::class);

        $controller->registerWithPassword($request);
    }

    public function test_request_password_reset_account_not_found()
    {
        $accountManager = Mockery::mock(AccountManager::class);
        $accountManager
            ->shouldReceive('getAccountByUsername')
            ->with('nonexistent@example.com')
            ->andReturn(null);

        $controller = $this->createController($accountManager);

        $request = Request::create('/forgot-password', 'POST', [
            'username' => 'nonexistent@example.com',
        ]);

        $this->expectException(ValidationException::class);

        $controller->requestPasswordReset($request);
    }

    public function test_request_password_reset_account_not_passworded()
    {
        // Create partial mock account to avoid database operations
        $account = Mockery::mock(Account::class)->makePartial();
        $account->is_passworded = false;

        $accountManager = Mockery::mock(AccountManager::class);
        $accountManager
            ->shouldReceive('getAccountByUsername')
            ->with('test@example.com')
            ->andReturn($account);

        $controller = $this->createController($accountManager);

        $request = Request::create('/forgot-password', 'POST', [
            'username' => 'test@example.com',
        ]);

        $this->expectException(ValidationException::class);

        $controller->requestPasswordReset($request);
    }

    public function test_request_password_reset_account_not_activated()
    {
        // Create partial mock account to avoid database operations
        $account = Mockery::mock(Account::class)->makePartial();
        $account->is_passworded = true;
        $account->shouldReceive('memberOf')->with(RoleConstants::Users)->andReturn(false);

        $accountManager = Mockery::mock(AccountManager::class);
        $accountManager
            ->shouldReceive('getAccountByUsername')
            ->with('test@example.com')
            ->andReturn($account);

        $controller = $this->createController($accountManager);

        $request = Request::create('/forgot-password', 'POST', [
            'username' => 'test@example.com',
        ]);

        $this->expectException(ValidationException::class);

        $controller->requestPasswordReset($request);
    }

    public function test_initiate_password_reset_from_email()
    {
        $controller = $this->createController();
        $request = Request::create('/reset-password/token123', 'GET', [
            'email' => 'test@example.com',
        ]);

        $response = $controller->initiatePasswordResetFromEmail($request, 'token123');

        $this->assertInstanceOf(\Illuminate\View\View::class, $response);
        $this->assertEquals('authentication.reset-password', $response->name());
        $this->assertEquals('token123', $response->getData()['token']);
        $this->assertEquals('test@example.com', $response->getData()['email']);
    }

    public function test_complete_password_reset_successful()
    {
        // Create a mock user that the callback will receive
        $mockUser = Mockery::mock(Account::class)->makePartial();
        $mockUser->id = 1; // Set ID for AuditTrailSubscriber
        $mockUser->shouldReceive('forceFill')->andReturnSelf();
        $mockUser->shouldReceive('save')->andReturn(true);

        FacadesPassword::shouldReceive('reset')
            ->andReturnUsing(function ($credentials, $callback) use ($mockUser) {
                // Call the callback with the mock user
                $callback($mockUser);
                return FacadesPassword::PASSWORD_RESET;
            });

        $controller = $this->createController();

        $request = Request::create('/reset-password', 'POST', [
            'email' => 'test@example.com',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
            'token' => 'valid-token',
        ]);

        $response = $controller->completePasswordReset($request, 'valid-token');

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals(route('login'), $response->getTargetUrl());
    }

    public function test_complete_password_reset_invalid_token()
    {
        FacadesPassword::shouldReceive('reset')
            ->andReturn(FacadesPassword::INVALID_TOKEN);

        $controller = $this->createController();

        $request = Request::create('/reset-password', 'POST', [
            'email' => 'test@example.com',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
            'token' => 'invalid-token',
        ]);

        $this->expectException(ValidationException::class);

        $controller->completePasswordReset($request, 'invalid-token');
    }
}
