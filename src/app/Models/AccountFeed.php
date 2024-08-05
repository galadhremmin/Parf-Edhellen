<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class AccountFeed extends ModelBase
{
    protected $fillable = ['account_id', 'happened_at', 'content_name', 'content_id', 'audit_trail_action_id', 'audit_trail_id'];
    protected $casts = [
        Model::CREATED_AT => 'datetime',
        Model::UPDATED_AT => 'datetime',
        'happened_at'     => 'datetime'
    ];

    use HasUuids;
    use Traits\HasAccount;

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function content()
    {
        return $this->morphTo();
    }

    public function audit_trail()
    {
        return $this->hasOne(AuditTrail::class);
    }
}
