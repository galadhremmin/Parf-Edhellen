<?php

namespace App\Subscribers;

use App\Models\Initialization\Morphs;
use App\Models\{
    Contribution,
    ForumThread,
    Gloss,
    ModelBase,
    Sentence,
    SystemError
};
use App\Events\{
    ContributionDestroyed,
    SentenceDestroyed,
    GlossDestroyed
};

class DiscussEventSubscriber
{
    /**
     * Register the listeners for the subscriber.
     *
     * @param  Illuminate\Events\Dispatcher  $events
     */
    public function subscribe($events)
    {
        $events->listen(
            ContributionDestroyed::class,
            self::class.'@onContributionDestroyed'
        );

        $events->listen(
            SentenceDestroyed::class,
            self::class.'@onSentenceDestroyed'
        );

        $events->listen(
            GlossDestroyed::class,
            self::class.'@onGlossDestroyed'
        );
    }

    /**
     * Handle the destruction of contributions.
     */
    public function onContributionDestroyed(ContributionDestroyed $event) 
    {
        $this->deleteThread($event->contribution);
    }

    /**
     * Handle the destruction of sentences.
     */
    public function onSentenceDestroyed(SentenceDestroyed $event) 
    {
        $this->deleteThread($event->sentence);
    }

    /**
     * Handle the destruction of glosses.
     */
    public function onGlossDestroyed(GlossDestroyed $event) 
    {
        $this->deleteThread($event->gloss, 
            $event->replacementGloss !== null ? $event->replacementGloss->id : 0
        );
    }
    
    /**
     * Deletes the thread associated with the specified entity. If a replacement is specified,
     * the existing thread is either repurposed for that entity, alternatively its posts are
     * re-associated with the replacement's thread.
     *
     * @param ModelBase $entity - the entity whose thread should be deleted
     * @param int $replaceWithId - the ID for an entity which should replace the aforementioned entity.
     * @return void
     */
    private function deleteThread(ModelBase $entity, int $replaceWithId = 0)
    {
        $morph = Morphs::getAlias($entity);
        $thread = ForumThread::where([
            ['entity_type', $morph],
            ['entity_id', $entity->id]
        ])->first();

        if ($thread === null) {
            return;
        }

        $delete = true;
        if ($replaceWithId) {

            // an replacement has been specified - attempt to find an existing thread
            // for the specified replacement entity.
            $existingThread = ForumThread::where([
                ['entity_type', $morph],
                ['entity_id', $replaceWithId]
            ])->first();

            if ($existingThread === null) {
                // no such thread exists - so repurpose the thread for the deleted entity 
                // to the replacement entity.
                $thread->entity_id = $replaceWithId;
                $thread->save();

                $delete = false;

            } else {
                // an thread for the replacement entity exists. This is somewhat problematic,
                // as there is likely already a discussion being conducted, and it would make
                // little sense for us to "inject" the posts from the deleted entity's thread
                // into an ongoing discussion, which is why the thread is deleted, along with 
                // its posts.
                //
                // The code beneath would inject the posts into the thread:
                /*
                $thread->forum_posts()->update([
                    'forum_thread_id' => $existingThread->id
                ]);
                */
            }
        }

        if ($delete) {
            // delete the thread and its posts.
            $thread->forum_posts()->delete();
            $thread->delete();
        }
    }
}
