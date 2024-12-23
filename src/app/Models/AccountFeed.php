<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class AccountFeed extends ModelBase
{
    use HasUuids;
    use Traits\HasAccount;

    protected $fillable = ['account_id', 'happened_at', 'content_type', 'content_id', 'audit_trail_action_id', 'audit_trail_id'];

    protected $casts = [
        Model::CREATED_AT => 'datetime',
        Model::UPDATED_AT => 'datetime',
        'happened_at' => 'datetime',
    ];

    protected $hidden = ['created_at', 'updated_at'];

    public function content(): MorphTo
    {
        return $this->morphTo();
    }

    public function audit_trail()
    {
        return $this->hasOne(AuditTrail::class);
    }
}
