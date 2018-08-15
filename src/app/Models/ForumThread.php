<?php

namespace App\Models;

class ForumThread extends ModelBase implements Interfaces\IHasFriendlyName
{
    protected $fillable = [ 
        'entity_type', 'entity_id', 'subject', 'account_id',
        'number_of_posts', 'number_of_likes', 'normalized_subject',
        'is_sticky'
    ];

    use Traits\HasAccount;

    public function entity() 
    {
        return $this->morphTo();
    }

    public function forum_posts()
    {
        return $this->hasMany(ForumPost::class);
    }

    public function getFriendlyName() 
    {
        return $this->subject;
    }
}
