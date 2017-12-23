<?php

namespace App\Subscribers;

use Illuminate\Support\Facades\Mail;

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
        $email = Account::where('id', $event->accountId)
            ->pluck('email')
            ->first();

        $rows = ForumPost::where('forum_thread_id', $event->post->forum_thread_id)
            ->join('accounts', 'accounts.id', 'forum_posts.account_id')
            ->select('email')
            ->distinct()
            ->get();
        
        $emails = [];
        foreach ($rows as $row) {
            if ($email !== $row->email) {
                $emails[] = $row->email;
            }
        }

        if (! count($emails)) {
            return;
        }

        $mail = new ForumPostCreatedMail($event->post);
        Mail::to($emails)->send($mail); // TODO: enqueue instead
    }
}
