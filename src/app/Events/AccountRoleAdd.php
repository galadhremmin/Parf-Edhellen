<?php

namespace App\Events;

use App\Models\Account;
use Illuminate\Queue\SerializesModels;

class AccountRoleAdd
{
    use SerializesModels;

    public function __construct(readonly Account $account, readonly string $role, readonly int $byAccountId)
    {}
}
