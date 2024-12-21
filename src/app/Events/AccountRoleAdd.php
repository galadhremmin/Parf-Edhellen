<?php

namespace App\Events;

use App\Models\Account;
use Illuminate\Queue\SerializesModels;

class AccountRoleAdd
{
    use SerializesModels;

    public Account $account;

    public string $role;

    public int $byAccountId;

    public function __construct(Account $account, string $role, int $byAccountId)
    {
        $this->account = $account;
        $this->role = $role;
        $this->byAccountId = $byAccountId;
    }
}
