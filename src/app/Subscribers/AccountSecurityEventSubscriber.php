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
        $assessment = $event->assessmentResult ?: [];
        if (isset($assessment['event'])) {
            // we don't need to store this information
            unset($assessment['event']);
        }
        
        AccountSecurityEvent::create([
            'account_id' => $event->account->id,
            'type' => $event->type,
            'assessment' => json_encode($assessment, JSON_PRETTY_PRINT),
            'result' => $event->result->value,
            'ip_address' => $event->ipAddress,
            'user_agent' => $event->userAgent,
        ]);
    }
}

