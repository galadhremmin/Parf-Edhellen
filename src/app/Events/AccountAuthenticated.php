<?php

namespace App\Events;

use App\Models\Account;
use Illuminate\Queue\SerializesModels;

class AccountAuthenticated
{
    use SerializesModels;

    public $account;
    public $firstTime;

    public function __construct(Account $account, bool $firstTime)
    {
        $this->account = $account;
        $this->firstTime = $firstTime;
    }
}
