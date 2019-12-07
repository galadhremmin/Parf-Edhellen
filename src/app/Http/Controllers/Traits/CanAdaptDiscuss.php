<?php

namespace App\Http\Controllers\Traits;

use Illuminate\Support\Collection;

use App\Adapters\DiscussAdapter;
use App\Repositories\ValueObjects\{
    ForumThreadsInGroupValue,
    ForumPostsInThreadValue,
    ForumThreadValue
};
use App\Models\{
    ForumThread,
    ForumPost
};

trait CanAdaptDiscuss 
{
    private $_discussAdapter;

    protected function __construct(DiscussAdapter $discussAdapter)
    {
        $this->_discussAdapter = $discussAdapter;
    }

    protected function adaptForumThreadsInGroup(ForumThreadsInGroupValue $data)
    {
        $this->_discussAdapter->adaptThreads($data->getThreads());
        return $data;
    }

    protected function adaptForumThreads(Collection $data)
    {
        $this->_discussAdapter->adaptThreads($data);
        return $data;
    }

    protected function adaptForumThread($data)
    {
        $thread = null;
        if ($data instanceof ForumThreadValue) {
            $thread = $data->getThread();
        } else if ($data instanceof ForumThread) {
            $thread = &$data;
        } else {
            throw new \Exception(sprintf('Unsupported entity %s.', get_class($data)));
        }

        $this->_discussAdapter->adaptThread($thread);
        return $data;
    }

    protected function adaptForumPosts(Collection $data)
    {
        $this->_discussAdapter->adaptPosts($data);
        return $data;
    }

    protected function adaptForumPost(ForumPost $data)
    {
        $this->_discussAdapter->adaptPost($data);
        return $data;
    }

    protected function adaptForumPostsInThread(ForumPostsInThreadValue $data)
    {
        $this->_discussAdapter->adaptPosts($data->getPosts());
        return $data;
    }

    protected function adaptAccountsPerForumGroup(Collection $data)
    {
        foreach ($data as $accounts) {
            foreach ($accounts as $account) {
                $this->_discussAdapter->adaptAccount($account);
            }
        }
        return $data;
    }
}