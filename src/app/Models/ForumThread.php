<?php

namespace App\Models;

class ForumThread extends ModelBase
{
    protected $fillable = [ 'entity_type', 'entity_id', 'subject', 'roles' ];

    public function entity() 
    {
        return $this->morphTo();
    }

    public function forum_posts()
    {
        return $this->hasMany(ForumPost::class);
    }
}
