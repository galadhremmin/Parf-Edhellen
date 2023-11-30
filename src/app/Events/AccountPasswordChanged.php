<?php

namespace App\Events;

use App\Models\Account;
use Illuminate\Queue\SerializesModels;

class AccountPasswordChanged
{
    use SerializesModels;

    public $account;
    public function __construct(Account $account)
    {
        $this->account = $account;
    }
}
