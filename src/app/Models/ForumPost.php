<?php

namespace App\Models;

class ForumPost extends ModelBase implements Interfaces\IHasFriendlyName
{
    use Traits\HasAccount;

    protected $fillable = [ 'forum_thread_id', 'parent_forum_post_id', 'number_of_likes', 'account_id', 'content' ];

    public function scopeActive($q) 
    {
        $q->where([
            ['is_deleted', 0],
            ['is_hidden', 0]
        ]);
    }

    public function forum_thread() 
    {
        return $this->belongsTo(ForumThread::class);
    }

    public function forum_post_likes() 
    {
        return $this->hasMany(ForumPostLike::class);
    }

    public function getFriendlyName() 
    {
        return $this->forum_thread->subject;
    }
}
