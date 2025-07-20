<?php

namespace App\Subscribers;

use App\Events\AccountAuthenticated;
use App\Events\AccountAvatarChanged;
use App\Events\AccountChanged;
use App\Events\AccountDestroyed;
use App\Events\AccountPasswordChanged;
use App\Events\AccountPasswordForgot;
use App\Events\AccountRoleAdd;
use App\Events\AccountRoleRemove;
use App\Events\AccountsMerged;
use App\Events\ContributionApproved;
use App\Events\ContributionDestroyed;
use App\Events\ContributionRejected;
use App\Events\EmailVerificationSent;
use App\Events\FlashcardFlipped;
use App\Events\ForumPostCreated;
use App\Events\ForumPostEdited;
use App\Events\ForumPostLikeCreated;
use App\Events\LexicalEntryCreated;
use App\Events\LexicalEntryDestroyed;
use App\Events\LexicalEntryEdited;
use App\Events\SentenceCreated;
use App\Events\SentenceDestroyed;
use App\Events\SentenceEdited;
use App\Models\AuditTrail;
use App\Repositories\Interfaces\IAuditTrailRepository;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Events\Verified;

class AuditTrailSubscriber
{
    private IAuditTrailRepository $_repository;

    public function __construct(IAuditTrailRepository $repository)
    {
        $this->_repository = $repository;
    }

    /**
     * Register the listeners for the subscriber.
     */
    public function subscribe()
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
            AccountPasswordForgot::class => 'onAccountPasswordForgot',
            PasswordReset::class => 'onAccountPasswordReset',
            Verified::class => 'onAccountEmailVerified',
            EmailVerificationSent::class => 'onAccountEmailVerificationSent',
            AccountRoleAdd::class => 'onAccountRoleAdded',
            AccountRoleRemove::class => 'onAccountRoleRemoved',

            FlashcardFlipped::class => 'onFlashcardFlipped',

            SentenceCreated::class => 'onSentenceCreated',
            SentenceEdited::class => 'onSentenceEdited',
            SentenceDestroyed::class => 'onSentenceDeleted',

            LexicalEntryCreated::class => 'onGlossCreated',
            LexicalEntryEdited::class => 'onGlossEdited',
            LexicalEntryDestroyed::class => 'onGlossDeleted',

            ContributionApproved::class => 'onContributionApproved',
            ContributionRejected::class => 'onContributionRejected',
            ContributionDestroyed::class => 'onContributionDestroyed',
        ];
    }

    /**
     * Handle the creation of a new forum post ("comment")
     */
    public function onForumPostCreated(ForumPostCreated $event): void
    {
        $this->_repository->store(AuditTrail::ACTION_COMMENT_ADD, $event->post, $event->accountId);
    }

    /**
     * Handle the editing of a new forum post ("comment")
     */
    public function onForumPostEdited(ForumPostEdited $event): void
    {
        $this->_repository->store(AuditTrail::ACTION_COMMENT_EDIT, $event->post, $event->accountId);
    }

    /**
     * Handle when an account is authenticated.
     */
    public function onAccountAuthenticated(AccountAuthenticated $event): void
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
    public function onAccountChanged(AccountChanged $event): void
    {
        $this->_repository->store(AuditTrail::ACTION_PROFILE_EDIT, $event->account);
    }

    public function onAccountDeleted(AccountDestroyed $event): void
    {
        $this->_repository->store(AuditTrail::ACTION_PROFILE_DELETE, $event->account, $event->accountId, true, [
            'nickname' => $event->friendlyName,
        ]);
    }

    /**
     * Handle the deletion of an account
     */
    public function onAccountDestroyed(AccountDestroyed $event): void
    {
        $this->_repository->store(AuditTrail::ACTION_PROFILE_DELETE, $event->account, $event->accountId, true);
    }

    /**
     * Handle new account registration.
     */
    public function onAccountCreated(Registered $event): void
    {
        $this->_repository->store(AuditTrail::ACTION_PROFILE_CREATED, $event->user);
    }

    public function onAccountsMerged(AccountsMerged $event): void
    {
        foreach ($event->accountsMerged as $account) {
            $this->_repository->store(AuditTrail::ACTION_PROFILE_MERGED, $account, $event->masterAccount->id, true);
        }
    }

    /**
     * Handle the creation of a new like
     */
    public function onForumPostLiked(ForumPostLikeCreated $event): void
    {
        $this->_repository->store(AuditTrail::ACTION_COMMENT_LIKE, $event->post, $event->accountId);
    }

    /**
     * Handle the editing of an account's avatar
     */
    public function onAccountAvatarChanged(AccountAvatarChanged $event): void
    {
        $this->_repository->store(AuditTrail::ACTION_PROFILE_EDIT_AVATAR, $event->account);
    }

    public function onAccountPasswordChanged(AccountPasswordChanged $event): void
    {
        $this->_repository->store(AuditTrail::ACTION_PROFILE_CHANGED_PASSWORD, $event->account, $event->account->id, true);
    }

    public function onAccountPasswordForgot(AccountPasswordForgot $event): void
    {
        $this->_repository->store(AuditTrail::ACTION_PROFILE_FORGOT_PASSWORD, $event->account, $event->account->id, true);
    }

    public function onAccountPasswordReset(PasswordReset $event): void
    {
        $this->_repository->store(AuditTrail::ACTION_PROFILE_RESET_PASSWORD, $event->user, $event->user->id, true);
    }

    public function onAccountEmailVerified(Verified $event): void
    {
        $this->_repository->store(AuditTrail::ACTION_MAIL_VERIFY_VERIFIED, $event->user, $event->user->id, true);
    }

    public function onAccountEmailVerificationSent(EmailVerificationSent $event): void
    {
        $this->_repository->store(AuditTrail::ACTION_MAIL_VERIFY_DISPATCHED, $event->user, $event->user->id, true);
    }

    public function onAccountRoleAdded(AccountRoleAdd $event): void
    {
        $this->_repository->store(AuditTrail::ACTION_ACCOUNT_ADD_ROLE, $event->account, $event->byAccountId, true, [
            'role' => $event->role,
        ]);
    }

    public function onAccountRoleRemoved(AccountRoleRemove $event): void
    {
        $this->_repository->store(AuditTrail::ACTION_ACCOUNT_REMOVE_ROLE, $event->account, $event->byAccountId, true, [
            'role' => $event->role,
        ]);
    }

    /**
     * Handle the flipping of flashcards.
     */
    public function onFlashcardFlipped(FlashcardFlipped $event): void
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

    public function onSentenceCreated(SentenceCreated $event): void
    {
        if ($event->accountId === 0) {
            return;
        }

        $this->_repository->store(AuditTrail::ACTION_SENTENCE_ADD, $event->sentence, $event->accountId);
    }

    public function onSentenceEdited(SentenceEdited $event): void
    {
        if ($event->accountId === 0) {
            return;
        }

        $this->_repository->store(AuditTrail::ACTION_SENTENCE_EDIT, $event->sentence, $event->accountId);
    }

    public function onSentenceDeleted(SentenceDestroyed $event): void
    {
        if ($event->accountId === 0) {
            return;
        }

        $this->_repository->store(AuditTrail::ACTION_SENTENCE_DELETE, $event->sentence, $event->accountId);
    }

    public function onGlossCreated(LexicalEntryCreated $event): void
    {
        if ($event->accountId === 0) {
            return;
        }

        $this->_repository->store(AuditTrail::ACTION_GLOSS_ADD, $event->lexicalEntry, $event->accountId);
    }

    public function onGlossEdited(LexicalEntryEdited $event): void
    {
        if ($event->accountId === 0) {
            return;
        }

        $this->_repository->store(AuditTrail::ACTION_GLOSS_EDIT, $event->lexicalEntry, $event->accountId);
    }

    public function onGlossDeleted(LexicalEntryDestroyed $event): void
    {
        if ($event->accountId === 0) {
            return;
        }

        $this->_repository->store(AuditTrail::ACTION_GLOSS_DELETE, $event->lexicalEntry, $event->accountId, true);
    }

    public function onContributionApproved(ContributionApproved $event): void
    {
        $this->_repository->store(AuditTrail::ACTION_CONTRIBUTION_APPROVE, $event->contribution, $event->contribution->reviewed_by_account_id, true);
    }

    public function onContributionRejected(ContributionRejected $event): void
    {
        $this->_repository->store(AuditTrail::ACTION_CONTRIBUTION_REJECT, $event->contribution, $event->contribution->reviewed_by_account_id, true);
    }

    public function onContributionDestroyed(ContributionDestroyed $event): void
    {
        // TODO: contribution technically doesn't exist, so need to figure this out.
        // $this->_repository->store(AuditTrail::ACTION_CONTRIBUTION_DELETE, $event->contribution, $event->accountId, true);
    }
}
