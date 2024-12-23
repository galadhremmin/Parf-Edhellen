<?php

namespace App\Events;

use App\Models\Account;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class AccountsMerged
{
    use SerializesModels;

    public Account $masterAccount;

    public Collection $accountsMerged;

    public function __construct(Account $masterAccount, Collection $accountsMerged)
    {
        $this->masterAccount = $masterAccount;
        $this->accountsMerged = $accountsMerged;
    }
}
