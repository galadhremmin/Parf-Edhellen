<?php

namespace App\Events;

use App\Models\Account;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class AccountsMerged
{
    use SerializesModels;

    public function __construct(readonly Account $masterAccount, readonly Collection $accountsMerged)
    {}
}
