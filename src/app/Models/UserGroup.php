<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserGroup extends Model
{
    protected $table = 'auth_groups';
    protected $primaryKey = 'ID';

    /**
     * Disable automatic timestamps.
     */
    public $timestamps = false;

    public function scopeForUser($query, User $user) 
    {
        $query->join('auth_accounts_groups as ag', $this->primaryKey, '=', 'ag.GroupID')
            ->where('ag.AccountID', $user->AccountID)
            ->select('Name', $this->primaryKey);
    }
}
