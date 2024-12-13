<?php

namespace App\Events;

use App\Models\Account;
use Illuminate\Queue\SerializesModels;

class AccountRoleRemove
{
    use SerializesModels;

    /**
     * @var Account
     */
    public $account;
    /**
     * @var string
     */
    public $role;
    /**
     * @var int
     */
    public $byAccountId;

    public function __construct(Account $account, string $role, int $byAccountId)
    {
        $this->account = $account;
        $this->role = $role;
        $this->byAccountId = $byAccountId;
    }
}
