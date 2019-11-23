<?php

namespace App\Repositories\ValueObjects;

class ForumThreadValue implements \JsonSerializable 
{
    use Traits\CanInitialize;
    use Traits\HasDiscussContext;
    use Traits\HasForumThread;

    public function __construct($properties)
    {
        $this->setupDiscussContext($properties);
        $this->setupForumThread($properties);
    }
}
