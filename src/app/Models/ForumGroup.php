<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;

class ForumGroup extends ModelBase
{
    protected $fillable = ['description', 'name', 'role', 'category', 'is_readonly'];

    public function forum_threads(): HasMany
    {
        return $this->hasMany(ForumThread::class);
    }
}
