<?php

namespace App\Providers;

use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
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
            \SocialiteProviders\Live\LiveExtendSocialite::class.'@handle',
            \SocialiteProviders\Discord\DiscordExtendSocialite::class.'@handle',
        ],
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
    ];

    /**
     * The subscriber classes to register.
     *
     * @var array
     */
    protected $subscribe = [
        \App\Subscribers\AccountSubscriber::class,
        \App\Subscribers\AuditTrailSubscriber::class,
        \App\Subscribers\ContributionMailEventSubscriber::class,
        \App\Subscribers\DiscussEventSubscriber::class,
        \App\Subscribers\DiscussMailEventSubscriber::class,
        \App\Subscribers\DiscussPostIndexerSubscriber::class,
        \App\Subscribers\GlossIndexerSubscriber::class,
        \App\Subscribers\SentenceIndexerSubscriber::class,
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
