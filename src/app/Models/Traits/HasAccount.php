<?php

namespace App\Models\Traits;

trait HasAccount
{
    public function scopeForAccount($query, int $accountId)
    {
        $query->where('account_id', $accountId);
    }
}
