<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\MorphOne;

class ForumDiscussion extends ModelBase
{
    use Traits\HasAccount;

    protected $fillable = ['account_id'];

    public function forum_thread(): MorphOne
    {
        return $this->morphOne(ForumThread::class, 'entity');
    }
}
