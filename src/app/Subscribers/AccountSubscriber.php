<?php

namespace App\Subscribers;

use Illuminate\Support\Facades\Cache;
use App\Events\AccountsMerged;
use App\Events\AccountDestroyed;
use App\Jobs\MigrateAccountData;
use App\Models\AuditTrail;

class AccountSubscriber
{
    /**
     * Register the listeners for the subscriber.
     */
    public function subscribe()
    {
        return [
            AccountsMerged::class => 'onAccountMerged',
            AccountDestroyed::class => 'onAccountDestroyed',
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

    public function onAccountDestroyed(AccountDestroyed $event): void
    {
        Cache::forget('ed.home.audit');
        Cache::forget('ed.home.statistics');
    }
}
