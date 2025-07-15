<?php

namespace App\Subscribers;

use App\Events\ForumPostCreated;
use App\Mail\ForumPostCreatedMail;
use App\Mail\ForumPostOnProfileMail;
use App\Models\Account;
use App\Models\ForumPost;
use App\Repositories\MailSettingRepository;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\Mail;

class DiscussMailEventSubscriber
{
    private MailSettingRepository $_mailSettingRepository;

    public function __construct(MailSettingRepository $mailSettingRepository)
    {
        $this->_mailSettingRepository = $mailSettingRepository;
    }

    /**
     * Register the listeners for the subscriber.
     */
    public function subscribe()
    {
        return [
            ForumPostCreated::class => 'onForumPostCreated',
            Verified::class => 'onAccountVerified',
        ];
    }

    /**
     * Notify subscribers of the new post.
     */
    public function onForumPostCreated(ForumPostCreated $event): void
    {
        $accountIds = ForumPost::where('forum_thread_id', $event->post->forum_thread_id)
            ->where('account_id', '<>', $event->accountId)
            ->distinct()
            ->pluck('account_id')
            ->toArray();

        $entity = $event->post->forum_thread->entity;
        if ($entity instanceof Account) {
            // The associated entity is in fact someone's profile, which should trigger a different event.
            // Notify the profile owner that someone has posted to their profile.
            $notified = $this->notifyNewPostOnProfile($entity->id, $event);

            // The associated profile owner should not receive two notifications.
            if ($notified) {
                $accountIds = array_filter($accountIds, function ($id) use ($entity) {
                    return $id !== $entity->id;
                });
            }

        } elseif ($entity->hasAttribute('account_id') && ! in_array($entity->account_id, $accountIds)) {
            // Also inform the originator of the affected entity of the new post.
            $accountIds[] = $entity->account_id;
        }

        $recipients = $this->_mailSettingRepository->qualify($accountIds, 'forum_post_created', $event->post->forum_thread);
        if (! $recipients->count()) {
            return;
        }

        foreach ($recipients as $recipient) {
            $cancellationToken = $this->_mailSettingRepository->generateCancellationToken($recipient->id, $event->post->forum_thread);
            $mail = new ForumPostCreatedMail($cancellationToken, $event->post);

            Mail::to($recipient->email)->queue($mail);
        }
    }

    public function onAccountVerified(Verified $event): void
    {
        /**
         * Even though the interface of `Verified` isn't compatible with `Account`, we know that 
         * the event will always be an instance of `Account` because the event is only dispatched
         * when an account is verified.
         * 
         * @var Account
         */
        $account = $event->user;
        $account->addMembershipTo(\App\Security\RoleConstants::Discuss);
    }

    private function notifyNewPostOnProfile(int $accountId, ForumPostCreated $event): bool
    {
        // Is the recipient the same as the sender, i.e. is the author writing on their own wall?
        if ($accountId === $event->accountId) {
            return true;
        }

        $recipients = $this->_mailSettingRepository->qualify([$accountId], 'forum_posted_on_profile', $event->post->forum_thread);
        if (! $recipients->count()) {
            return false;
        }

        foreach ($recipients as $recipient) {
            $cancellationToken = $this->_mailSettingRepository->generateCancellationToken($recipient->id, $event->post->forum_thread);
            $mail = new ForumPostOnProfileMail($cancellationToken, $event->post);

            Mail::to($recipient->email)->queue($mail);
        }

        return true;
    }
}
