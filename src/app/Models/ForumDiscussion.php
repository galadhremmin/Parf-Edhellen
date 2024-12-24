<?php

namespace App\Models;

class ForumDiscussion extends ModelBase
{
    use Traits\HasAccount;

    protected $fillable = ['account_id'];

    public function forum_thread()
    {
        return $this->morphOne(ForumThread::class, 'entity');
    }
}
