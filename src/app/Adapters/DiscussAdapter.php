<?php

namespace App\Adapters;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

use App\Http\Discuss\ContextFactory;
use App\Helpers\MarkdownParser;
use App\Models\ForumPost;

class DiscussAdapter
{
    private $_contextFactory;

    public function __construct(ContextFactory $contextFactory)
    {
        $this->_contextFactory = $contextFactory;
    }

    public function adaptThreads(Collection $threads)
    {
        $user = Auth::user();
        $contextFactory = $this->_contextFactory;

        // remove threads that the user is not authorised to see.
        $adapted = $threads->filter(function ($thread) use($user, $contextFactory) {
            $context = $contextFactory->create($thread->entity_type);
            return $context !== null && $context->available($thread->entity_id, $user);
        });

        return $adapted;
    }

    public function adaptPost(ForumPost $post)
    {
        $parser = new MarkdownParser();
        $post->content = $parser->parse($post->content);

        return $post;
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
