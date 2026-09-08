<?php

namespace Tests\Unit\Models;

use App\Models\Account;
use Illuminate\Auth\SessionGuard;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Http\Request;
use Illuminate\Session\ArraySessionHandler;
use Illuminate\Session\Store;
use Mockery;
use Tests\TestCase;

/**
 * Laravel validates the "remember me" cookie by comparing the hash embedded in it with the
 * account's password hash. Feeding it a null password crashes every single request made by the
 * account's browser, which is what these tests guard against.
 */
class AccountRecallerTest extends TestCase
{
    private const REMEMBER_TOKEN = 'ZQgTthAZDC2ZP0bNkvS6bpO0tnTKF0tNNPTgj2ChOXWN4WtEKfGkDvUBexen';

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    private function createGuard(Account $account, string $recallerHash): SessionGuard
    {
        $provider = Mockery::mock(UserProvider::class);
        $provider->shouldReceive('retrieveByToken')
            ->with($account->id, self::REMEMBER_TOKEN)
            ->andReturn($account);

        $session = new Store('parf_edhellen_session', new ArraySessionHandler(120));
        $guard = new SessionGuard('web', $provider, $session);

        $request = Request::create('/');
        $request->cookies->set($guard->getRecallerName(),
            $account->id.'|'.self::REMEMBER_TOKEN.'|'.$recallerHash
        );

        return $guard->setRequest($request);
    }

    private function createAccount(?string $password): Account
    {
        $account = new Account;
        $account->id = 8220;
        $account->password = $password;
        $account->remember_token = self::REMEMBER_TOKEN;

        return $account;
    }

    public function test_passwordless_account_with_a_legacy_cookie_does_not_crash()
    {
        // Cookies issued before Laravel 12.45 carry an empty hash, as it was the account's
        // password hash -- which used to be null for every account.
        $account = $this->createAccount(null);
        $guard = $this->createGuard($account, '');

        $this->assertSame($account, $guard->user());
    }

    public function test_passworded_account_with_a_legacy_cookie_is_rejected()
    {
        $account = $this->createAccount('$2y$10$abcdefghijklmnopqrstuv');
        $guard = $this->createGuard($account, '');

        $this->assertNull($guard->user());
    }

    public function test_passworded_account_with_a_current_cookie_is_accepted()
    {
        $account = $this->createAccount('$2y$10$abcdefghijklmnopqrstuv');
        $guard = $this->createGuard($account, '');

        $guard = $this->createGuard($account,
            $guard->hashPasswordForCookie($account->getAuthPassword())
        );

        $this->assertSame($account, $guard->user());
    }

    public function test_cookie_from_a_different_password_is_rejected()
    {
        $account = $this->createAccount('$2y$10$abcdefghijklmnopqrstuv');
        $guard = $this->createGuard($account, '');

        // The account changed its password since the cookie was issued.
        $guard = $this->createGuard($account,
            $guard->hashPasswordForCookie('$2y$10$vutsrqponmlkjihgfedcba')
        );

        $this->assertNull($guard->user());
    }
}
