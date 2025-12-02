<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccountSecurityEvent extends ModelBase
{
    protected $table = 'account_security_events';

    protected $fillable = [
        'account_id',
        'type',
        'assessment',
        'result',
        'ip_address',
        'user_agent',
    ];

    /**
     * @return BelongsTo<Account>
     */
    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}

