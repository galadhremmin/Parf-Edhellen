<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    public function scopeForAccount($query, Account $account) 
    {
        $query->join('account_role_rels as ag', 'id', '=', 'ag.role_id')
            ->where('ag.account_id', $account->id)
            ->select('name', 'id');
    }
}
