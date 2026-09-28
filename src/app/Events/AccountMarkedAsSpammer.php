<?php

namespace App\Events;

use App\Models\Account;
use Illuminate\Queue\SerializesModels;

class AccountMarkedAsSpammer
{
    use SerializesModels;

    public function __construct(readonly Account $account, readonly int $actingAccountId)
    {}
}
