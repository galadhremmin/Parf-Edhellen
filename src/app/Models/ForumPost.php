<?php

namespace App\Models;

class ForumPost extends ModelBase implements Interfaces\IHasFriendlyName
{
    use Traits\HasAccount;

    protected $fillable = [ 'forum_thread_id', 'parent_forum_post_id', 'number_of_likes', 'account_id', 'content' ];

    public function forum_thread() 
    {
        return $this->belongsTo(ForumThread::class);
    }

    public function likes() 
    {
        return $this->hasMany(ForumPostLike::class);
    }

    public function getFriendlyName() 
    {
        return $this->forum_thread->subject;
    }
}
