<?php

namespace App\Repositories\ValueObjects;

class ForumThreadsInGroupValue implements \JsonSerializable
{
    use Traits\CanInitialize;
    use Traits\HasForumGroup;
    use Traits\HasForumPagination;

    public function __construct($properties)
    {
        $this->setupForumGroup($properties);
        $this->setupForumPagination($properties);
        $this->initialize($properties, 'threads');
    }

    public function getThreads()
    {
        return $this->getValue('threads');
    }
}
