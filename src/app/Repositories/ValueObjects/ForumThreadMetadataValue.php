<?php

namespace App\Repositories\ValueObjects;

class ForumThreadMetadataValue implements \JsonSerializable
{
    use Traits\CanInitialize;

    public function __construct($properties)
    {
        $this->initializeAll($properties, [
            'forum_post_id', 'likes', 'likes_per_post'
        ]);
    }

    public function getForumPostId()
    {
        return $this->getValue('forum_post_id');
    }

    public function getLikes()
    {
        return $this->getValue('likes');
    }

    public function getLikesPerPost()
    {
        return $this->getValue('likes_per_post');
    }
}
