<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccountFeedRefreshTime extends ModelBase
{
    protected $fillable = ['account_id', 'feed_content_type', 'oldest_happened_at', 'newest_happened_at'];

    protected $casts = [
        Model::CREATED_AT => 'datetime',
        Model::UPDATED_AT => 'datetime',
        'oldest_happened_at' => 'datetime',
        'newest_happened_at' => 'datetime',
    ];

    use Traits\HasAccount;

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function scopeForUniverse($query)
    {
        $query->where('feed_content_type', 'universe');
    }
}
