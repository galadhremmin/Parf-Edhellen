<?php

namespace App\Repositories\ValueObjects;

class ForumThreadsForPostsValue implements \JsonSerializable
{
    use Traits\CanInitialize;

    public function __construct($properties)
    {
        $this->initializeAll($properties, [
            'forum_threads', 'forum_groups',
        ]);
    }

    public function getThreads()
    {
        return $this->getValue('forum_threads');
    }

    public function getGroups()
    {
        return $this->getValue('forum_groups');
    }
}
