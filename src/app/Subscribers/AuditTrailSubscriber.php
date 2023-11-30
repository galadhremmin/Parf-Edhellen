<?php

namespace App\Subscribers;

use App\Repositories\Interfaces\IAuditTrailRepository;
use App\Models\AuditTrail;
use App\Events\{
    AccountAuthenticated,
    AccountChanged,
    AccountAvatarChanged,
    AccountPasswordChanged,
    ForumPostCreated,
    ForumPostEdited,
    ForumPostLikeCreated,
    FlashcardFlipped,
    SentenceCreated,
    SentenceEdited,
    GlossCreated,
    GlossEdited
};

class AuditTrailSubscriber
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

        $events->listen(
            ForumPostEdited::class,
            self::class.'@onForumPostEdited'
        );

        $events->listen(
            ForumPostLikeCreated::class,
            self::class.'@onForumPostLiked'
        );

        $events->listen(
            AccountAuthenticated::class,
            self::class.'@onAccountAuthenticated'
        );

        $events->listen(
            AccountChanged::class,
            self::class.'@onAccountChanged'
        );

        $events->listen(
            AccountAvatarChanged::class,
            self::class.'@onAccountAvatarChanged'
        );

        $events->listen(
            AccountPasswordChanged::class,
            self::class."@onAccountPasswordChanged"
        );

        $events->listen(
            FlashcardFlipped::class,
            self::class.'@onFlashcardFlipped'
        );

        $events->listen(
            SentenceCreated::class,
            self::class.'@onSentenceCreated'
        );

        $events->listen(
            SentenceEdited::class,
            self::class.'@onSentenceEdited'
        );

        $events->listen(
            GlossCreated::class,
            self::class.'@onGlossCreated'
        );

        $events->listen(
            GlossEdited::class,
            self::class.'@onGlossEdited'
        );
    }

    /**
     * Handle the creation of a new forum post ("comment")
     */
    public function onForumPostCreated(ForumPostCreated $event) 
    {
        $this->repository()->store(AuditTrail::ACTION_COMMENT_ADD, $event->post, $event->accountId);
    }

    /**
     * Handle the editing of a new forum post ("comment")
     */
    public function onForumPostEdited(ForumPostEdited $event) 
    {
        $this->repository()->store(AuditTrail::ACTION_COMMENT_EDIT, $event->post, $event->accountId);
    }

    /**
     * Handle when an account is authenticated.
     */
    public function onAccountAuthenticated(AccountAuthenticated $event)
    {
        $eventId = $event->firstTime 
            ? AuditTrail::ACTION_PROFILE_FIRST_TIME
            : AuditTrail::ACTION_PROFILE_AUTHENTICATED;

        // Register an audit trail for the user logging in.
        $this->repository()->store($eventId, $event->account, $event->account->id);
    }

    /**
     * Handle the creation of a new like
     */
    public function onForumPostLiked(ForumPostLikeCreated $event) 
    {
        $this->repository()->store(AuditTrail::ACTION_COMMENT_LIKE, $event->post, $event->accountId);
    }

    /**
     * Handle the editing of an account's profile
     */
    public function onAccountChanged(AccountChanged $event)
    {
        $this->repository()->store(AuditTrail::ACTION_PROFILE_EDIT, $event->account);
    }

    /**
     * Handle the editing of an account's avatar
     */
    public function onAccountAvatarChanged(AccountAvatarChanged $event)
    {
        $this->repository()->store(AuditTrail::ACTION_PROFILE_EDIT_AVATAR, $event->account);
    }

    public function onAccountPasswordChanged(AccountPasswordChanged $event)
    {
        $this->repository()->store(AuditTrail::ACTION_PROFILE_CHANGED_PASSWORD, $event->account);
    }

    /**
     * Handle the flipping of flashcards.
     */
    public function onFlashcardFlipped(FlashcardFlipped $event) 
    {
        $qualifyingAction = 0;
        switch ($event->numberOfCards) {
            case 1:
                $qualifyingAction = AuditTrail::ACTION_FLASHCARD_FIRST_CARD;
                break;
            case 10:
                $qualifyingAction = AuditTrail::ACTION_FLASHCARD_CARD_10;
                break;
            case 50:
                $qualifyingAction = AuditTrail::ACTION_FLASHCARD_CARD_50;
                break;
            case 100:
                $qualifyingAction = AuditTrail::ACTION_FLASHCARD_CARD_100;
                break;
            case 200:
                $qualifyingAction = AuditTrail::ACTION_FLASHCARD_CARD_200;
                break;
            case 500:
                $qualifyingAction = AuditTrail::ACTION_FLASHCARD_CARD_500;
                break;
        }

        if ($qualifyingAction !== 0) {
            $this->repository()->store($qualifyingAction, $event->result);
        }
    }

    public function onSentenceCreated(SentenceCreated $event) 
    {
        if ($event->accountId === 0) {
            return;
        }

        $this->repository()->store(AuditTrail::ACTION_SENTENCE_ADD, $event->sentence, $event->accountId);
    }

    public function onSentenceEdited(SentenceEdited $event) 
    {
        if ($event->accountId === 0) {
            return;
        }

        $this->repository()->store(AuditTrail::ACTION_SENTENCE_EDIT, $event->sentence, $event->accountId);
    }

    public function onGlossCreated(GlossCreated $event) 
    {
        if ($event->accountId === 0) {
            return;
        }

        $this->repository()->store(AuditTrail::ACTION_GLOSS_ADD, $event->gloss, $event->accountId);
    }

    public function onGlossEdited(GlossEdited $event) 
    {
        if ($event->accountId === 0) {
            return;
        }

        $this->repository()->store(AuditTrail::ACTION_GLOSS_EDIT, $event->gloss, $event->accountId);
    }

    private function repository()
    {
        return resolve(IAuditTrailRepository::class);
    }
}
