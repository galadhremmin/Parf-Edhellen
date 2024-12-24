<?php

namespace App\Repositories\ValueObjects;

class ForumPostsInThreadValue implements \JsonSerializable
{
    use Traits\CanInitialize;
    use Traits\HasForumPagination;

    public function __construct(array $properties)
    {
        $this->setupForumPagination($properties);
        $this->initializeAll($properties, [
            'posts', 'thread_id', 'thread_post_id', 'jump_post_id', 'no_of_posts', 'no_of_posts_per_page',
        ]);
    }

    public function getPosts()
    {
        return $this->getValue('posts');
    }

    public function getThreadId()
    {
        return $this->getValue('thread_id');
    }

    public function getThreadPostId()
    {
        return $this->getValue('thread_post_id');
    }

    public function getJumpPostId()
    {
        return $this->getValue('jump_post_id');
    }
}
