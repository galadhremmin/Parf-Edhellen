<?php

namespace App\Subscribers;

use Illuminate\Support\Facades\Mail;

use App\Repositories\MailSettingRepository;
use App\Models\Initialization\Morphs;
use App\Models\{
    Account,
    Contribution
};
use App\Events\{
    ContributionApproved,
    ContributionRejected
};
use App\Mail\{
    ContributionApprovedMail,
    ContributionRejectedMail
};

class ContributionMailEventSubscriber
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
            ContributionApproved::class,
            self::class.'@onContributionApproved'
        );

        $events->listen(
            ContributionRejected::class,
            self::class.'@onContributionRejected'
        );
    }

    /**
     * Notify the author of the approved contribution of its approval.
     */
    public function onContributionApproved(ContributionApproved $event) 
    {
        $recipients = $this->repository()->qualify([$event->contribution->account_id], 'forum_contribution_approved', $event->contribution);
        if (! $recipients->count()) {
            return;
        }
        
        foreach ($recipients as $recipient) {
            $cancellationToken = $this->repository()->generateCancellationToken($recipient->id, $event->contribution);
            $mail = new ContributionApprovedMail($cancellationToken, $event->contribution);
            
            Mail::to($recipient->email)->queue($mail);
        }
    }

    /**
     * Notify the author of the rejected contribution of its rejection.
     */
    public function onContributionRejected(ContributionRejected $event) 
    {
        $recipients = $this->repository()->qualify([$event->contribution->account_id], 'forum_contribution_rejected', $event->contribution);
        if (! $recipients->count()) {
            return;
        }
        
        foreach ($recipients as $recipient) {
            $cancellationToken = $this->repository()->generateCancellationToken($recipient->id, $event->contribution);
            $mail = new ContributionRejectedMail($cancellationToken, $event->contribution);
            
            Mail::to($recipient->email)->queue($mail);
        }
    }

    private function repository()
    {
        return resolve(MailSettingRepository::class);
    }
}
