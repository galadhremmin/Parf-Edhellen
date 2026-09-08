<?php

namespace Tests\Unit\Models;

use App\Models\Account;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AccountAuthenticationTest extends TestCase
{
    public function test_auth_password_returns_the_password_hash()
    {
        $account = new Account;
        $account->password = '$2y$10$abcdefghijklmnopqrstuv';

        $this->assertSame('$2y$10$abcdefghijklmnopqrstuv', $account->getAuthPassword());
    }

    public function test_auth_password_returns_an_empty_string_for_passwordless_accounts()
    {
        $account = new Account;

        $this->assertSame('', $account->getAuthPassword());
    }

    /**
     * Laravel compares this value with the hash embedded in the "remember me" cookie, which fails
     * with a TypeError when the value is null.
     */
    public function test_auth_password_is_always_a_string()
    {
        $account = new Account;

        $this->assertIsString($account->getAuthPassword());
        $this->assertFalse(hash_equals($account->getAuthPassword(), 'de4dbeef'));
        $this->assertIsString(hash_hmac('sha256', $account->getAuthPassword(), 'key'));
    }

    /**
     * An empty password hash must never validate, as it is what passwordless accounts report.
     */
    public function test_no_password_validates_against_a_passwordless_account()
    {
        $account = new Account;

        $this->assertFalse(Hash::check('', $account->getAuthPassword()));
        $this->assertFalse(Hash::check(' ', $account->getAuthPassword()));
        $this->assertFalse(Hash::check('password', $account->getAuthPassword()));
    }
}
