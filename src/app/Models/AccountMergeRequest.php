<?php

namespace App\Models;

class AccountMergeRequest extends ModelBase
{
    protected $fillable = [
        'id',
        'account_id',
        'account_ids',
        'verification_token',
        'requester_account_id',
        'requester_ip',
        'is_fulfilled'
    ];

    protected $hidden = ['verification_token', 'requester_account_id', 'requester_ip'];

    use Traits\HasAccount;

    public function requester_account()
    {
        return $this->belongsTo(Account::class, 'requester_account_id', 'id');
    }
}
