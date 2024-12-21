<?php

namespace App\Events;

use App\Models\Account;
use Illuminate\Queue\SerializesModels;

class AccountDestroyed
{
    use SerializesModels;

    public Account $account;

    public string $friendlyName;

    public int $accountId;

    public function __construct(Account $account, string $friendlyName, int $accountId)
    {
        $this->account = $account;
        $this->friendlyName = $friendlyName;
        $this->accountId = $accountId;
    }
}
