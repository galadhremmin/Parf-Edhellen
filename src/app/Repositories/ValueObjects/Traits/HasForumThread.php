<?php

namespace App\Repositories\ValueObjects\Traits;

use App\Models\ForumThread;

trait HasForumThread 
{
    public function setupForumThread(array $properties)
    {
        $this->initializeAll($properties, [
            'thread',
            'thread_id'
        ]);
    }

    /**
     * @return ForumThread
     */
    public function getThread() 
    {
        return $this->getValue('thread');
    }

    /**
     * @return int
     */
    public function getThreadId() 
    {
        return $this->getValue('thread_id');
    }
}
