<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecaptchaAssessment extends ModelBase
{
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
