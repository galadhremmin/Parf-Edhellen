<?php

namespace App\Repositories\ValueObjects;

class ForumThreadForEntityValue implements \JsonSerializable
{
    use Traits\CanInitialize;
    use Traits\HasForumThread;

    public function __construct($properties)
    {
        $this->setupForumThread($properties);
        $this->initialize($properties, 'forum_post_id');
    }

    public function getForumPostId()
    {
        return $this->getValue('forum_post_id');
    }
}
