<?php

namespace App\Subscribers;

use App\Events\ContributionDestroyed;
use App\Events\GlossDestroyed;
use App\Events\SentenceDestroyed;
use App\Models\ForumThread;
use App\Models\Initialization\Morphs;
use App\Models\ModelBase;

class DiscussEventSubscriber
{
    /**
     * Register the listeners for the subscriber.
     */
    public function subscribe()
    {
        return [
            ContributionDestroyed::class => 'onContributionDestroyed',
            SentenceDestroyed::class => 'onSentenceDestroyed',
            GlossDestroyed::class => 'onGlossDestroyed',
        ];
    }

    /**
     * Handle the destruction of contributions.
     */
    public function onContributionDestroyed(ContributionDestroyed $event): void
    {
        $this->deleteThread($event->contribution);
    }

    /**
     * Handle the destruction of sentences.
     */
    public function onSentenceDestroyed(SentenceDestroyed $event): void
    {
        $this->deleteThread($event->sentence);
    }

    /**
     * Handle the destruction of glosses.
     */
    public function onGlossDestroyed(GlossDestroyed $event): void
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
     * @param  ModelBase  $entity  - the entity whose thread should be deleted
     * @param  int  $replaceWithId  - the ID for an entity which should replace the aforementioned entity.
     */
    private function deleteThread(ModelBase $entity, int $replaceWithId = 0): void
    {
        $morph = Morphs::getAlias($entity);
        $thread = ForumThread::where([
            ['entity_type', $morph],
            ['entity_id', $entity->id],
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
                ['entity_id', $replaceWithId],
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
