<?php

namespace App\Models\Traits;
use App\Models\Account;

trait HasAccount
{
    public function scopeForAccount($query, $accountOrId)
    {
        $accountId = $this->getAccountId($accountOrId);
        $query->where('account_id', $accountId);
    }

    public function scopeForAccounts($query, array $accounts)
    {
        $accountIds = [];
        foreach ($accounts as $account) {
            $accountIds[] = $this->getAccountId($account);
        }

        $query->whereIn('account_id', $accountIds);
    }

    public function account() 
    {
        return $this->belongsTo(Account::class);
    }

    private function getAccountId($accountOrId)
    {
        if (is_numeric($accountOrId)) {
            return intval($accountOrId);
        }
        
        if ($accountOrId instanceOf Account) {
            return $accountOrId->id;
        }

        throw new \Exception('Invalid account parameter "'.serialize($accountOrId).'".');
    }
}
