<?php

namespace App\Models;

class ForumPost extends ModelBase
{
    use Traits\HasAccount;

    protected $fillable = [ 'forum_context_id', 'entity_id', 'parent_forum_post_id', 'number_of_likes', 'account_id', 'content',
        'context_name', 'entity_name' ];

    public function forum_context() 
    {
        return $this->belongsTo(ForumContext::class);
    }

    public function account() 
    {
        return $this->belongsTo(Account::class);
    }

    public function likes() 
    {
        return $this->hasMany(ForumPostLike::class);
    }
}
