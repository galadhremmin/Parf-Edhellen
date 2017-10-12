<?php

namespace App\Adapters;

use Illuminate\Support\Collection;
use App\Http\RouteResolving\RouteResolverFactory;
use App\Models\ForumThread;

class AuthorAdapter
{
    private $_routeResolverFactory;

    public function __construct(RouteResolverFactory $routeResolverFactory)
    {
        $this->_routeResolverFactory = $routeResolverFactory;
    }

    public function adapt(Collection $posts)
    {
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

            $iconPath = $this->_routeResolverFactory->create($post->forum_thread->entity_type)
                ->getIconPath();
            
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
