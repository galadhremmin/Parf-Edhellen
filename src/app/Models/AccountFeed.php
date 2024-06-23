<?php

namespace App\Models;

class AccountFeed extends ModelBase
{
    protected $fillable = ['account_id', 'happened_at', 'content_name', 'content_id', 'audit_trail_action_id', 'audit_trail_id'];

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
