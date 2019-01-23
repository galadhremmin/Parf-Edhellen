<?php

namespace App\Adapters;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

use App\Http\Discuss\ContextFactory;
use App\Helpers\MarkdownParser;
use App\Models\{
    Account,
    ForumPost,
    ForumThread
};

class DiscussAdapter
{
    private $_contextFactory;

    public function __construct(ContextFactory $contextFactory)
    {
        $this->_contextFactory = $contextFactory;
    }

    public function adaptAccount(Account $account)
    {
        if ($account->has_avatar) {
            $account->setAttribute('avatar_path', sprintf('/storage/avatars/%d.png', $account->id));
        }
    }

    public function adaptPost(ForumPost $post)
    {
        $this->adaptAccount($post->account);

        $parser = new MarkdownParser();
        $post->content = $parser->parse($post->content);

        return $post;
    }

    public function adaptPosts(Collection $posts)
    {
        $posts->map(function ($post) {
            $this->adaptPost($post);
        });
    }

    public function adaptThread(ForumThread $thread)
    {
        $this->adaptAccount($thread->account);
    }

    public function adaptThreads(Collection $threads)
    {
        $threads->map(function ($thread) {
            $this->adaptThread($thread);
        });
    }

    public function adaptForTimeline(Collection $posts)
    {
        $user = Auth::user();
        $thread_id = 0;
        $inverted   = true;

        $adapted = [];
        $i = 0;
        foreach ($posts as $post)
        {
            if ($thread_id !== $post->forum_thread_id) {
                $i = 0;

                $inverted  = ! $inverted; 
                $thread_id = $post->forum_thread_id;
            }

            $context = $this->_contextFactory->create($post->forum_thread->entity_type);
            if (! $context->available($post, $user)) {
                continue; // skip unavailable posts
            }

            $iconPath = $context->getIconPath();
            $adapted[] = (object) [
                'id'              => $post->id,
                'subject'         => $post->forum_thread->subject,
                'icon'            => $iconPath,
                'created_at'      => $post->updated_at ?: $post->created_at,
                'content'         => $post->content,
                'number_of_likes' => $post->number_of_likes,
                'i'               => $i,
                'inverted'        => $inverted
            ];

            $i += 1;
        }

        return $adapted;
    }
}
