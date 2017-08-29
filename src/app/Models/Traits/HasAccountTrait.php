<?php

namespace App\Models\Traits;

trait HasAccountTrait 
{
    public function scopeForAccount($query, int $accountId)
    {
        $query->where('account_id', $accountId);
    }
}