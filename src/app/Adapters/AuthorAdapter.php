<?php

namespace App\Adapters;

use Illuminate\Support\Collection;

class AuthorAdapter
{
    public function adapt(Collection $posts)
    {
        $context_id = 0;
        $entity_id  = 0;
        $inverted   = true;

        $adapted = [];
        $i = 0;
        foreach ($posts as $post)
        {
            if ($context_id !== $post->forum_context_id || $entity_id !== $post->entity_id) {
                $i = 0;

                $inverted   = ! $inverted; 
                $context_id = $post->forum_context_id;
                $entity_id  = $post->entity_id;
            }

            $adapted[] = (object) [
                'id'              => $post->id,
                'entity_name'     => $post->entity_name,
                'context_name'    => $post->context_name,
                'context'         => $post->forum_context->name,
                'icon'            => $post->forum_context->icon,
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
