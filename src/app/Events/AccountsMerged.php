<?php

namespace App\Events;

use App\Models\Account;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class AccountsMerged
{
    use SerializesModels;

    /**
     * @var Account
     */
    public $masterAccount;
    /**
     * @var Collection
     */
    public $accountsMerged;

    public function __construct(Account $masterAccount, Collection $accountsMerged)
    {
        $this->masterAccount = $masterAccount;
        $this->accountsMerged = $accountsMerged;
    }
}
