<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WebAuthnCredential extends Model
{
    use HasFactory;

    protected $table = 'webauthn_credentials';

    protected $fillable = [
        'account_id',
        'credential_id',
        'public_key',
        'counter',
        'display_name',
        'transport',
        'is_active',
        'last_used_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_used_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relationship: A credential belongs to an account
     *
     * @return BelongsTo<Account>
     */
    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    /**
     * Get only active credentials
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Update counter and last_used_at timestamp
     */
    public function recordUsage(int $newCounter): void
    {
        $this->update([
            'counter' => $newCounter,
            'last_used_at' => Carbon::now(),
        ]);
    }
}
