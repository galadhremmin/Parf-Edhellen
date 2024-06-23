<?php

namespace App\Models;

class AccountFeedRefreshTime extends ModelBase
{
    protected $fillable = ['account_id', 'feed_content_name', 'oldest_happened_at', 'newest_happened_at'];

    use Traits\HasAccount;

    public function account()
    {
        return $this->belongsTo(Account::class);
    }
}
