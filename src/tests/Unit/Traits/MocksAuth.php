<?php

namespace Tests\Unit\Traits;

use Tests\TestCase;
use Auth;

use App\Models\Account;

trait MocksAuth
{
    protected function setUp()
    {
        Auth::shouldReceive('user')->andReturn($user = Account::findOrFail(1));  
    }
}