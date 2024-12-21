<?php

namespace App\Events;

use App\Models\Account;
use Illuminate\Queue\SerializesModels;

class AccountAuthenticated
{
    use SerializesModels;

    public Account $account;

    public bool $firstTime;

    public function __construct(Account $account, bool $firstTime)
    {
        $this->account = $account;
        $this->firstTime = $firstTime;
    }
}
