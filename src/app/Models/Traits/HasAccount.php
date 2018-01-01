<?php

namespace App\Models\Traits;
use App\Models\Account;

trait HasAccount
{
    public function scopeForAccount($query, int $accountId)
    {
        $query->where('account_id', $accountId);
    }

    public function scopeForAccounts($query, array $accountIds)
    {
        $query->whereIn('account_id', $accountIds);
    }

    public function account() 
    {
        return $this->belongsTo(Account::class);
    }
}
