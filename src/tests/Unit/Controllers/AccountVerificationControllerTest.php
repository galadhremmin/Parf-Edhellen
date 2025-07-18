<?php
namespace Tests\Unit\Controllers;

use App\Events\EmailVerificationSent;
use App\Http\Controllers\AccountVerificationController;
use App\Models\Account;
use App\Repositories\SystemErrorRepository;
use App\Security\RoleConstants;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\URL;
use Mockery;
use Tests\TestCase;

class AccountVerificationControllerTest extends TestCase
{
    use DatabaseTransactions;

    public function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_verify_account_sends_email_and_redirects()
    {
        $user = Mockery::mock(Account::class);
        $user->shouldReceive('sendEmailVerificationNotification')->once();
        $request = Mockery::mock(Request::class);
        $request->shouldReceive('user')->andReturn($user);

        Event::fake([EmailVerificationSent::class]);

        $systemErrorRepository = Mockery::mock(SystemErrorRepository::class);
        $controller = new AccountVerificationController($systemErrorRepository);

        $response = $controller->verifyAccount($request);

        Event::assertDispatched(EmailVerificationSent::class);
        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertStringContainsString('verification=sent', $response->getTargetUrl());
    }

    public function test_verify_account_handles_exception_and_redirects()
    {
        $user = Mockery::mock(Account::class);
        $user->shouldReceive('sendEmailVerificationNotification')->andThrow(new \Exception('fail'));
        $request = Mockery::mock(Request::class);
        $request->shouldReceive('user')->andReturn($user);

        $systemErrorRepository = Mockery::mock(SystemErrorRepository::class);
        $systemErrorRepository->shouldReceive('saveException')->once();
        $controller = new AccountVerificationController($systemErrorRepository);

        $response = $controller->verifyAccount($request);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertStringContainsString('verification=sent', $response->getTargetUrl());
    }

    public function test_confirm_verification_from_email_already_verified()
    {
        $user = Mockery::mock(Account::class);
        $user->shouldReceive('hasVerifiedEmail')->andReturn(true);
        $request = Mockery::mock(EmailVerificationRequest::class);
        $request->shouldReceive('user')->andReturn($user);

        $systemErrorRepository = Mockery::mock(SystemErrorRepository::class);
        $controller = new AccountVerificationController($systemErrorRepository);

        $response = $controller->confirmVerificationFromEmail($request);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertStringNotContainsString('verification=ok', $response->getTargetUrl());
    }

    public function test_confirm_verification_from_email_marks_verified_and_dispatches_event()
    {
        $user = Mockery::mock(Account::class);
        $user->shouldReceive('hasVerifiedEmail')->andReturn(false);
        $user->shouldReceive('markEmailAsVerified')->andReturn(true);
        $request = Mockery::mock(EmailVerificationRequest::class);
        $request->shouldReceive('user')->andReturn($user);

        Event::fake([Verified::class]);

        $systemErrorRepository = Mockery::mock(SystemErrorRepository::class);
        $controller = new AccountVerificationController($systemErrorRepository);

        $response = $controller->confirmVerificationFromEmail($request);

        Event::assertDispatched(Verified::class);
        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertStringContainsString('verification=ok', $response->getTargetUrl());
    }

    public function test_confirm_verification_and_discuss_role_is_assigned()
    {
        // Integration test: use real models and HTTP request
        /** @var Account */
        $user = Account::factory()->createOne();
        $user->addMembershipTo(RoleConstants::Users);

        $this->actingAs($user);

        // Ensure user is not verified and does not have Discuss role
        $this->assertFalse($user->hasVerifiedEmail());
        $this->assertFalse($user->memberOf(RoleConstants::Discuss));

        // Simulate the verification request
        $response = $this->get(URL::signedRoute('verification.verify', [
            'id' => $user->getKey(),
            'hash' => sha1($user->getEmailForVerification()),
        ]));

        $response->assertRedirect(route('account.security', ['verification' => 'ok']));

        $user->refresh();
        $this->assertTrue($user->hasVerifiedEmail());
        $this->assertTrue($user->memberOf(RoleConstants::Discuss));
    }
}
