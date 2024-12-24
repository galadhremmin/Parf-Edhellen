<?php

namespace Tests\Unit\Traits;

use App\Models\Account;
use Mockery;

trait MocksAuth
{
    protected function setUp()
    {
        $authManager = Mockery::mock('Illuminate\Auth\AuthManager');

        $account = Account::findOrFail(1);
        $authManager->shouldReceive('user')->andReturn($account);
        $authManager->shouldReceive('check')->andReturn(true);

        $this->app->instance('auth', $authManager);
    }
}
