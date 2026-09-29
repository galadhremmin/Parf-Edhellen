<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccountSecurityEvent extends ModelBase
{
    protected $table = 'account_security_events';

    protected $fillable = [
        'account_id',
        'authenticated_account_id',
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

    /**
     * The account that actually authenticated, when the security event is
     * recorded against a different (e.g. master) account. Null otherwise.
     *
     * @return BelongsTo<Account>
     */
    public function authenticatedAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'authenticated_account_id');
    }
}

