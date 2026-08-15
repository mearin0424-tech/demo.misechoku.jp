<?php

namespace App\Providers;

use App\Listeners\UpdateLastLoginAt;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        // Update casts.last_login_at / shop_managers.last_login_at on any
        // successful login across every entry point (standard, LINE, mock LINE,
        // demo). Login is dispatched only when auth actually succeeds.
        Login::class => [
            UpdateLastLoginAt::class,
        ],
        \SocialiteProviders\Manager\SocialiteWasCalled::class => [
            \SocialiteProviders\Line\LineExtendSocialite::class.'@handle',
        ],

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
     *
     * @return bool
     */
    public function shouldDiscoverEvents()
    {
        return false;
    }
}
