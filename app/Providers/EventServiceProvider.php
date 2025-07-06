<?php

namespace App\Providers;

use App\Listeners\CreateSessionOnLogin;
use App\Listeners\UpdateSessionOnLogout;
use App\Events\ReformeCreated;
use App\Events\ReformeUpdated;
use App\Events\ActiviteCreated;
use App\Events\ActiviteCompleted;
use App\Events\DeadlineApproaching;
use App\Listeners\SendReformeCreatedNotification;
use App\Listeners\SendReformeUpdatedNotification;
use App\Listeners\SendActiviteCreatedNotification;
use App\Listeners\SendActiviteCompletedNotification;
use App\Listeners\SendDeadlineNotification;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        // Événements d'authentification
        Login::class => [
            CreateSessionOnLogin::class,
        ],

        Logout::class => [
            UpdateSessionOnLogout::class,
        ],

        // Événements de notifications
        ReformeCreated::class => [
            SendReformeCreatedNotification::class,
        ],

        ReformeUpdated::class => [
            SendReformeUpdatedNotification::class,
        ],

        ActiviteCreated::class => [
            SendActiviteCreatedNotification::class,
        ],

        ActiviteCompleted::class => [
            SendActiviteCompletedNotification::class,
        ],

        DeadlineApproaching::class => [
            SendDeadlineNotification::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
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
