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
    ForumPostCreatedMail
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
        
        $emails = $this->repository()->qualify($accountIds, 'forum_post_created', $event->post->forum_thread);
        if (! count($emails)) {
            return;
        }
        
        $mail = new ForumPostCreatedMail($event->post);
        Mail::to($emails)->queue($mail);
    }

    private function repository()
    {
        return resolve(MailSettingRepository::class);
    }
}
