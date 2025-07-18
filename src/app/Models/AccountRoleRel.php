<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccountRoleRel extends ModelBase
{
    protected $fillable = ['account_id', 'role_id'];

    use Traits\HasAccount;

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }
}
