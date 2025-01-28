<?php

namespace App\Providers;

use App\Events\TaskCompleted;
use App\Events\TaskCreated;
use App\Events\UserExperienceChanged;
use App\Listeners\AddTaskToUsers;
use App\Listeners\UpdateUserExperience;
use App\Listeners\UpdateUserLevel;
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
        TaskCreated::class => [
            AddTaskToUsers::class,
        ],
        TaskCompleted::class => [
            UpdateUserExperience::class,
        ],
        UserExperienceChanged::class => [
            UpdateUserLevel::class,
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
