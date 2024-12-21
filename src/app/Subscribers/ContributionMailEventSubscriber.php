<?php

namespace App\Subscribers;

use App\Events\ContributionApproved;
use App\Events\ContributionRejected;
use App\Mail\ContributionApprovedMail;
use App\Mail\ContributionRejectedMail;
use App\Repositories\MailSettingRepository;
use Illuminate\Support\Facades\Mail;

class ContributionMailEventSubscriber
{
    private MailSettingRepository $_mailSettingRepository;

    public function __construct(MailSettingRepository $mailSettingRepository)
    {
        $this->_mailSettingRepository = $mailSettingRepository;
    }

    /**
     * Register the listeners for the subscriber.
     *
     * @param  Illuminate\Events\Dispatcher  $events
     */
    public function subscribe($events)
    {
        return [
            ContributionApproved::class => 'onContributionApproved',
            ContributionRejected::class => 'onContributionRejected',
        ];
    }

    /**
     * Notify the author of the approved contribution of its approval.
     */
    public function onContributionApproved(ContributionApproved $event): void
    {
        $recipients = $this->_mailSettingRepository->qualify([$event->contribution->account_id], 'forum_contribution_approved', $event->contribution);
        if (! $recipients->count()) {
            return;
        }

        foreach ($recipients as $recipient) {
            $cancellationToken = $this->_mailSettingRepository->generateCancellationToken($recipient->id, $event->contribution);
            $mail = new ContributionApprovedMail($cancellationToken, $event->contribution);

            Mail::to($recipient->email)->queue($mail);
        }
    }

    /**
     * Notify the author of the rejected contribution of its rejection.
     */
    public function onContributionRejected(ContributionRejected $event): void
    {
        $recipients = $this->_mailSettingRepository->qualify([$event->contribution->account_id], 'forum_contribution_rejected', $event->contribution);
        if (! $recipients->count()) {
            return;
        }

        foreach ($recipients as $recipient) {
            $cancellationToken = $this->_mailSettingRepository->generateCancellationToken($recipient->id, $event->contribution);
            $mail = new ContributionRejectedMail($cancellationToken, $event->contribution);

            Mail::to($recipient->email)->queue($mail);
        }
    }
}
