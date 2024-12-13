<?php

namespace App\Subscribers;

use App\Repositories\Interfaces\IAuditTrailRepository;
use App\Models\AuditTrail;
use App\Events\{
    AccountAuthenticated,
    AccountChanged,
    AccountAvatarChanged,
    AccountDestroyed,
    AccountPasswordChanged,
    AccountsMerged,
    ForumPostCreated,
    ForumPostEdited,
    ForumPostLikeCreated,
    FlashcardFlipped,
    SentenceCreated,
    SentenceEdited,
    GlossCreated,
    GlossDestroyed,
    GlossEdited,
    SentenceDestroyed
};
use Illuminate\Auth\Events\Registered;

class AuditTrailSubscriber
{
    /**
     * Audit trail repository
     * @var IAuditTrailRepository
     */
    private $_repository;

    public function __construct(IAuditTrailRepository $repository)
    {
        $this->_repository = $repository;
    }

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
            ForumPostLikeCreated::class => 'onForumPostLiked',
            AccountAuthenticated::class => 'onAccountAuthenticated',
            AccountChanged::class => 'onAccountChanged',
            AccountDestroyed::class => 'onAccountDeleted',
            Registered::class => 'onAccountCreated',
            AccountsMerged::class => 'onAccountsMerged',
            AccountAvatarChanged::class => 'onAccountAvatarChanged',
            AccountPasswordChanged::class => 'onAccountPasswordChanged',
            FlashcardFlipped::class => 'onFlashcardFlipped',
            SentenceCreated::class => 'onSentenceCreated',
            SentenceEdited::class => 'onSentenceEdited',
            SentenceDestroyed::class => 'onSentenceDeleted',
            GlossCreated::class => 'onGlossCreated',
            GlossEdited::class => 'onGlossEdited',
            GlossDestroyed::class => 'onGlossDeleted'
        ];
    }

    /**
     * Handle the creation of a new forum post ("comment")
     */
    public function onForumPostCreated(ForumPostCreated $event) 
    {
        $this->_repository->store(AuditTrail::ACTION_COMMENT_ADD, $event->post, $event->accountId);
    }

    /**
     * Handle the editing of a new forum post ("comment")
     */
    public function onForumPostEdited(ForumPostEdited $event) 
    {
        $this->_repository->store(AuditTrail::ACTION_COMMENT_EDIT, $event->post, $event->accountId);
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
        $this->_repository->store($eventId, $event->account, $event->account->id);
    }

    /**
     * Handle the editing of an account's profile
     */
    public function onAccountChanged(AccountChanged $event)
    {
        $this->_repository->store(AuditTrail::ACTION_PROFILE_EDIT, $event->account);
    }

    /**
     * Handle the deletion of an account
     */
    public function onAccountDestroyed(AccountDestroyed $event)
    {
        $this->_repository->store(AuditTrail::ACTION_PROFILE_DELETE, $event->account, $event->accountId, true);
    }

    /**
     * Handle new account registration.
     */
    public function onAccountCreated(Registered $event)
    {
        $this->_repository->store(AuditTrail::ACTION_PROFILE_CREATED, $event->user);
    }

    public function onAccountsMerged(AccountsMerged $event)
    {
        foreach ($event->accountsMerged as $account) {
            $this->_repository->store(AuditTrail::ACTION_PROFILE_MERGED, $account, $event->masterAccount->id, true);
        }
    }

    /**
     * Handle the creation of a new like
     */
    public function onForumPostLiked(ForumPostLikeCreated $event) 
    {
        $this->_repository->store(AuditTrail::ACTION_COMMENT_LIKE, $event->post, $event->accountId);
    }

    /**
     * Handle the editing of an account's avatar
     */
    public function onAccountAvatarChanged(AccountAvatarChanged $event)
    {
        $this->_repository->store(AuditTrail::ACTION_PROFILE_EDIT_AVATAR, $event->account);
    }

    public function onAccountPasswordChanged(AccountPasswordChanged $event)
    {
        $this->_repository->store(AuditTrail::ACTION_PROFILE_CHANGED_PASSWORD, $event->account, $event->account->id, true);
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
            $this->_repository->store($qualifyingAction, $event->result);
        }
    }

    public function onSentenceCreated(SentenceCreated $event) 
    {
        if ($event->accountId === 0) {
            return;
        }

        $this->_repository->store(AuditTrail::ACTION_SENTENCE_ADD, $event->sentence, $event->accountId);
    }

    public function onSentenceEdited(SentenceEdited $event) 
    {
        if ($event->accountId === 0) {
            return;
        }

        $this->_repository->store(AuditTrail::ACTION_SENTENCE_EDIT, $event->sentence, $event->accountId);
    }

    public function onSentenceDeleted(SentenceDestroyed $event)
    {
        if ($event->accountId === 0) {
            return;
        }

        $this->_repository->store(AuditTrail::ACTION_SENTENCE_DELETE, $event->sentence, $event->accountId);
    }

    public function onGlossCreated(GlossCreated $event) 
    {
        if ($event->accountId === 0) {
            return;
        }

        $this->_repository->store(AuditTrail::ACTION_GLOSS_ADD, $event->gloss, $event->accountId);
    }

    public function onGlossEdited(GlossEdited $event) 
    {
        if ($event->accountId === 0) {
            return;
        }

        $this->_repository->store(AuditTrail::ACTION_GLOSS_EDIT, $event->gloss, $event->accountId);
    }

    public function onGlossDeleted(GlossDestroyed $event)
    {
        if ($event->accountId === 0) {
            return;
        }
        
        $this->_repository->store(AuditTrail::ACTION_GLOSS_DELETE, $event->gloss, $event->accountId, true);
    }
}
