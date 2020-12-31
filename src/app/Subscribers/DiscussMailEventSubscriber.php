<?php

namespace App\Subscribers;

use Illuminate\Support\Facades\Mail;

use App\Repositories\MailSettingRepository;
use App\Models\Initialization\Morphs;
use App\Models\{
    Account,
    ForumPost,
    ForumThread
};
use App\Events\{
    ForumPostCreated
};
use App\Mail\{
    ForumPostCreatedMail,
    ForumPostOnProfileMail
};

class DiscussMailEventSubscriber
{
    /**
     * Register the listeners for the subscriber.
     *
     * @param  Illuminate\Events\Dispatcher  $events
     */
    public function subscribe($events)
    {
        if (config('app.env') !== 'production') {
            return;
        }

        $events->listen(
            ForumPostCreated::class,
            self::class.'@onForumPostCreated'
        );
    }

    /**
     * Notify subscribers of the new post.
     */
    public function onForumPostCreated(ForumPostCreated $event) 
    {
        $accountIds = ForumPost::where('forum_thread_id', $event->post->forum_thread_id)
            ->where('account_id', '<>', $event->accountId)
            ->select('account_id')
            ->distinct()
            ->get()
            ->pluck('account_id')
            ->toArray();
        
        $entity = $event->post->forum_thread->entity;
        if ($entity instanceof Account) {
            // The associated entity is in fact someone's profile, which should trigger a different event.
            // Notify the profile owner that someone has posted to their profile.
            $notified = $this->notifyNewPostOnProfile($entity->id, $event);

            // The associated profile owner should not receive two notifications.
            if ($notified) {
                $accountIds = array_filter($accountIds, function ($id) use($entity) {
                    return $id !== $entity->id;
                });
            }

        } else if ($entity->hasAttribute('account_id') && ! in_array($entity->account_id, $accountIds)) {
            // Also inform the originator of the affected entity of the new post.
            $accountIds[] = $entity->account_id;
        }
        
        $recipients = $this->repository()->qualify($accountIds, 'forum_post_created', $event->post->forum_thread);
        if (! $recipients->count()) {
            return;
        }
        
        foreach ($recipients as $recipient) {
            $cancellationToken = $this->repository()->generateCancellationToken($recipient->id, $event->post->forum_thread);
            $mail = new ForumPostCreatedMail($cancellationToken, $event->post);
            
            Mail::to($recipient->email)->queue($mail);
        }
    }

    private function notifyNewPostOnProfile(int $accountId, ForumPostCreated $event)
    {
        // Is the recipient the same as the sender, i.e. is the author writing on their own wall?
        if ($accountId === $event->accountId) {
            return true;
        }

        $recipients = $this->repository()->qualify([$accountId], 'forum_posted_on_profile', $event->post->forum_thread);
        if (! $recipients->count()) {
            return false;
        }
        
        foreach ($recipients as $recipient) {
            $cancellationToken = $this->repository()->generateCancellationToken($recipient->id, $event->post->forum_thread);
            $mail = new ForumPostOnProfileMail($cancellationToken, $event->post);
            
            Mail::to($recipient->email)->queue($mail);
        }

        return true;
    }

    private function repository()
    {
        return resolve(MailSettingRepository::class);
    }
}
