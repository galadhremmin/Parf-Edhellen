<?php

namespace App\Models;

class ForumPostLike extends ModelBase
{
    use Traits\HasAccountTrait;

    protected $fillable = [ 'forum_post_id', 'account_id' ];

    public function account() 
    {
        return $this->belongsTo(Account::class);
    }

    public function scopeForPost($query, int $postId, $accountId = 0) 
    {
        $query->where('forum_post_id', $postId);

        if ($accountId !== 0) {
            $query->where('account_id', $accountId);
        }
    }
}
