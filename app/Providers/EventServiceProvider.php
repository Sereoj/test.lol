<?php

namespace App\Providers;

use App\Events\CommentCreated;
use App\Events\FileDownloaded;
use App\Events\GifPublished;
use App\Events\ImagePublished;
use App\Events\NotificationSettingsUpdated;
use App\Events\PostPublished;
use App\Events\ProfileComplected;
use App\Events\SubscriptionActivated;
use App\Events\SubscriptionCancelled;
use App\Events\TaskCompleted;
use App\Events\TaskCreated;
use App\Events\UserActivity;
use App\Events\UserExperienceChanged;
use App\Events\VideoPublished;
use App\Listeners\ActivatePremiumFeatures;
use App\Listeners\AddTaskToUsers;
use App\Listeners\DeactivatePremiumFeatures;
use App\Listeners\UpdateUserTasksOnCommentCreated;
use App\Listeners\HandleFileDownloaded;
use App\Listeners\HandleNotificationSettingsUpdated;
use App\Listeners\HandleProfileComplected;
use App\Listeners\PostPublishedListener;
use App\Listeners\UpdateOnlineStatus;
use App\Listeners\UpdateUserExperience;
use App\Listeners\UpdateUserLevel;
use App\Listeners\UpdateUserTasksOnPostPublished;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

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
        UserActivity::class => [
            UpdateOnlineStatus::class,
        ],
        TaskCreated::class => [
            AddTaskToUsers::class, //Как только создается задача, сразу присваивается к пользователю.
        ],
        TaskCompleted::class => [
            UpdateUserExperience::class, // Как только задача выполняется, сразу присваивает опыт.
        ],
        UserExperienceChanged::class => [
            UpdateUserLevel::class, // Как только обновляется опыт, присваивается уровень.
        ],
        PostPublished::class => [
            PostPublishedListener::class, //Как только пользователь, публикует, пост переходит в статус опубликовано.
            UpdateUserTasksOnPostPublished::class, //Автоматически выполняется задача
        ],
        ImagePublished::class => [
            UpdateUserTasksOnPostPublished::class, //как только публикуется изображение, задача сразу выполняется.
        ],
        GifPublished::class => [
            UpdateUserTasksOnPostPublished::class,
        ],
        VideoPublished::class => [
            UpdateUserTasksOnPostPublished::class,
        ],
        CommentCreated::class => [
            UpdateUserTasksOnCommentCreated::class,
        ],
        ProfileComplected::class => [
            HandleProfileComplected::class,
        ],
        FileDownloaded::class => [
            HandleFileDownloaded::class,
        ],
        NotificationSettingsUpdated::class => [
            HandleNotificationSettingsUpdated::class,
        ],
        SubscriptionActivated::class => [
            ActivatePremiumFeatures::class,
        ],
        SubscriptionCancelled::class => [
            DeactivatePremiumFeatures::class,
        ]
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
