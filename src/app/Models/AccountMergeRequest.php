<?php

namespace App\Models;

class AccountMergeRequest extends ModelBase
{
    use Traits\HasAccount;
    use Traits\HasUuidId;

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

    public function requester_account()
    {
        return $this->belongsTo(Account::class, 'requester_account_id', 'id');
    }
}
