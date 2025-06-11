<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ForumPost extends ModelBase implements Interfaces\IHasFriendlyName
{
    use Traits\HasAccount;

    protected $fillable = ['forum_thread_id', 'parent_forum_post_id', 'number_of_likes', 'account_id', 'content'];

    public function scopeActive($q)
    {
        $q->where([
            ['is_deleted', 0],
            ['is_hidden', 0],
        ]);
    }

    /**
     * @return BelongsTo<ForumThread>
     */
    public function forum_thread(): BelongsTo
    {
        return $this->belongsTo(ForumThread::class);
    }

    /**
     * @return BelongsTo<AccountFeed>
     */
    public function account_feed(): BelongsTo
    {
        return $this->belongsTo(AccountFeed::class);
    }

    /**
     * @return HasMany<ForumPostLike>
     */
    public function forum_post_likes(): HasMany
    {
        return $this->hasMany(ForumPostLike::class);
    }

    public function getFriendlyName()
    {
        return $this->forum_thread->subject;
    }
}
