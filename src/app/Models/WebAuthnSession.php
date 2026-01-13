<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WebAuthnSession extends Model
{
    use HasFactory;

    protected $table = 'webauthn_sessions';

    protected $fillable = [
        'challenge',
        'account_id',
        'email',
        'session_type',
        'challenge_data',
        'expires_at',
    ];

    protected $casts = [
        'challenge_data' => 'json',
        'expires_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relationship: A session may belong to an account (NULL for login)
     *
     * @return BelongsTo<Account>
     */
    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class)->withDefault();
    }

    /**
     * Check if session is still valid (not expired)
     */
    public function isValid(): bool
    {
        return $this->expires_at === null || $this->expires_at->isFuture();
    }

    /**
     * Scope to find by challenge
     */
    public function scopeByChallenge($query, string $challenge)
    {
        return $query->where('challenge', $challenge)->first();
    }

    /**
     * Scope for registration sessions
     */
    public function scopeRegistration($query)
    {
        return $query->where('session_type', 'registration');
    }

    /**
     * Scope for authentication sessions
     */
    public function scopeAuthentication($query)
    {
        return $query->where('session_type', 'authentication');
    }
}
