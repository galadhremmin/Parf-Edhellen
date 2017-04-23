<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    public function scopeForUser($query, User $user) 
    {
        $query->join('account_role_rels as ag', 'id', '=', 'ag.role_id')
            ->where('ag.account_id', $user->id)
            ->select('name', 'id');
    }
}
