<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        \SocialiteProviders\Manager\SocialiteWasCalled::class => [
            // add your listeners (aka providers) here
            'SocialiteProviders\Live\LiveExtendSocialite@handle',
            'SocialiteProviders\Discord\DiscordExtendSocialite@handle'
        ]
    ];

    /**
     * The subscriber classes to register.
     *
     * @var array
     */
    protected $subscribe = [
        \App\Subscribers\AuditTrailSubscriber::class,
        \App\Subscribers\DiscussEventSubscriber::class,
        \App\Subscribers\DiscussMailEventSubscriber::class,
        \App\Subscribers\ContributionMailEventSubscriber::class
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        //
    }
}
