<?php

namespace App\Subscribers;

use App\Events\AccountsMerged;
use App\Jobs\MigrateAccountData;

class AccountSubscriber
{
    /**
     * Register the listeners for the subscriber.
     */
    public function subscribe()
    {
        return [
            AccountsMerged::class => 'onAccountMerged',
        ];
    }

    /**
     * Handle the destruction of contributions.
     */
    public function onAccountMerged(AccountsMerged $event): void
    {
        foreach ($event->accountsMerged as $account) {
            MigrateAccountData::dispatch($account->id, $event->masterAccount->id) //
                ->onQueue('accounts');
        }
    }
}
