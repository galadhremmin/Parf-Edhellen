<?php

namespace App\Subscribers;

use App\Events\ForumPostCreated;
use App\Events\ForumPostEdited;
use App\Jobs\ProcessDiscussIndex;
use App\Models\ForumPost;

class DiscussPostIndexerSubscriber
{
    /**
     * Register the listeners for the subscriber.
     *
     * @param  Illuminate\Events\Dispatcher  $events
     */
    public function subscribe($events)
    {
        return [
            ForumPostCreated::class => 'onForumPostCreated',
            ForumPostEdited::class => 'onForumPostEdited',
        ];
    }

    public function onForumPostCreated(ForumPostCreated $event): void
    {
        $this->update($event->post);
    }

    public function onForumPostEdited(ForumPostEdited $event): void
    {
        $this->update($event->post);
    }

    private function update(ForumPost $post): void
    {
        ProcessDiscussIndex::dispatch($post)->onQueue('indexing');
    }
}
