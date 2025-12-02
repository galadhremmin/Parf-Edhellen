<?php

namespace App\Subscribers;

use App\Events\AccountSecurityActivity;
use App\Models\AccountSecurityEvent;

class AccountSecurityEventSubscriber
{
    /**
     * Register the listeners for the subscriber.
     */
    public function subscribe()
    {
        return [
            AccountSecurityActivity::class => 'onAccountSecurityActivity',
        ];
    }

    /**
     * Handle the AccountSecurityActivity event.
     */
    public function onAccountSecurityActivity(AccountSecurityActivity $event): void
    {
        AccountSecurityEvent::create([
            'account_id' => $event->account->id,
            'type' => $event->type,
            'assessment' => $event->assessmentResult !== null 
                ? json_encode($event->assessmentResult, JSON_PRETTY_PRINT) 
                : json_encode([], JSON_PRETTY_PRINT),
            'result' => $event->result->value,
            'ip_address' => $event->ipAddress,
            'user_agent' => $event->userAgent,
        ]);
    }
}

