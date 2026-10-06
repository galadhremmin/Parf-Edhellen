<?php

namespace App\Events;

use App\Models\Account;
use Illuminate\Queue\SerializesModels;

class AccountAuthenticated
{
    use SerializesModels;

    public function __construct(readonly Account $account, readonly bool $firstTime)
    {}
}
