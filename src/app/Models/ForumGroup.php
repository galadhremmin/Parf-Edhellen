<?php

namespace App\Models;

class ForumGroup extends ModelBase
{
    protected $fillable = [ 'description', 'name', 'role', 'category' ];

    public function forum_threads()
    {
        return $this->hasMany(ForumThread::class);
    }
}
