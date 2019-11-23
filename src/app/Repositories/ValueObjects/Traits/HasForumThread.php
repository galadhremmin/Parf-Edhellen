<?php

namespace App\Repositories\ValueObjects\Traits;

use App\Models\ForumThread;

trait HasForumThread 
{
    public function setupForumThread(array $properties)
    {
        $this->initializeAll($properties, [
            'thread'
        ]);
    }

    /**
     * @return ForumThread
     */
    public function getThread() 
    {
        return $this->getValue('thread');
    }
}
