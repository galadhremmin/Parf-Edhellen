<?php

namespace App\Models;

class ForumGroup extends ModelBase
{
    protected $fillable = [ 'description', 'name', 'role' ];

    public function forum_threads()
    {
        return $this->hasMany(ForumThread::class);
    }
}
