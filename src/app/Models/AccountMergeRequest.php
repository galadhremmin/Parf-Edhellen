<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
        'is_fulfilled',
        'is_error',
        'error',
    ];

    protected $hidden = ['verification_token', 'requester_account_id', 'requester_ip'];

    protected $casts = [
        Model::CREATED_AT => 'datetime',
        Model::UPDATED_AT => 'datetime',
        'is_fulfilled' => 'boolean',
        'is_error' => 'boolean',
    ];

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function requester_account()
    {
        return $this->belongsTo(Account::class, 'requester_account_id', 'id');
    }
}
