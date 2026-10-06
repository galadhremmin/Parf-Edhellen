<?php

namespace App\Events;

use App\Models\Account;
use Illuminate\Queue\SerializesModels;

class AccountDestroyed
{
    use SerializesModels;

    public function __construct(readonly Account $account, readonly string $friendlyName, readonly int $accountId)
    {}
}
