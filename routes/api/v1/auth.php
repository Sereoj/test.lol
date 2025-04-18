<?php

use App\Http\Controllers\Apps\AppController;
use App\Http\Controllers\Authentication\AccountRecoveryController;
use App\Http\Controllers\Authentication\AuthController;
use App\Http\Controllers\Authentication\StepController;
use App\Http\Controllers\Billing\BalanceController;
use App\Http\Controllers\Billing\PurchaseController;
use App\Http\Controllers\Billing\SubscriptionController;
use App\Http\Controllers\Billing\TransactionController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ChallengeController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\EmploymentStatusController;
use App\Http\Controllers\Media\AvatarController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Posts\MediaController;
use App\Http\Controllers\Posts\PostController;
use App\Http\Controllers\Posts\PostStatisticController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\Users\UserAccountController;
use App\Http\Controllers\Users\UserAchievementController;
use App\Http\Controllers\Users\UserBadgeController;
use App\Http\Controllers\Users\UserCoverController;
use App\Http\Controllers\Users\UserEmploymentStatusController;
use App\Http\Controllers\Users\UserFollowController;
use App\Http\Controllers\Users\UserGenderController;
use App\Http\Controllers\Users\UserLocationController;
use App\Http\Controllers\Users\UserNotificationSettingsController;
use App\Http\Controllers\Users\UserPersonalizationController;
use App\Http\Controllers\Users\UserPostController;
use App\Http\Controllers\Users\UserSettingsController;
use App\Http\Controllers\Users\UserSkillController;
use App\Http\Controllers\Users\UserSourceController;
use App\Http\Controllers\Users\UserStatusController;
use App\Http\Controllers\Users\UserTaskController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Auth Routes (v1)
|--------------------------------------------------------------------------
| Маршруты, требующие авторизации пользователя
| Все эти маршруты защищены middleware auth:api
|
*/

Route::middleware('auth:api')->group(function () {
    Route::get('/gender', [UserGenderController::class, 'index'])
        ->name('gender');

    // Маршруты аутентификации
    Route::prefix('auth')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout'])
            ->name('logout');
        Route::get('/me', [AuthController::class, 'user'])
            ->name('auth.me');

        // Шаги регистрации пользователя
        Route::prefix('step')->group(function () {
            Route::post('/one', [StepController::class, 'one'])
                ->name('step.one');
            Route::post('/two', [StepController::class, 'two'])
                ->name('step.two');
            Route::post('/three', [StepController::class, 'three'])
                ->name('step.three');
        });
    });

    // Маршруты пользователя
    Route::prefix('user')->group(function () {
        Route::get('/', [AuthController::class, 'user'])
            ->name('user.user');

        Route::get('/me', [AuthController::class, 'user'])
            ->name('user.user.me');

        // Баланс пользователя
        Route::prefix('balance')->group(function () {
            Route::get('/', [BalanceController::class, 'getBalance']);
            Route::post('/topup', [BalanceController::class, 'topUpBalance']);
            Route::post('/withdraw', [BalanceController::class, 'withdrawBalance']);
            Route::post('/transfer', [BalanceController::class, 'transferBalance']);
        });

        // Транзакции
        Route::get('/transactions', [TransactionController::class, 'getTransactions']);

        // Покупки
        Route::post('/posts/{postId}/purchase', [PurchaseController::class, 'purchasePost']);

        // Подписки
        Route::prefix('subscriptions')->group(function () {
            Route::post('/', [SubscriptionController::class, 'createSubscription']);
            Route::get('/active', [SubscriptionController::class, 'getActiveSubscription']);
            Route::post('/{subscriptionId}/extend', [SubscriptionController::class, 'extendSubscription']);
        });

        // Статус занятости пользователя
        Route::prefix('employment-status')->group(function () {
            Route::post('/assign', [UserEmploymentStatusController::class, 'assignEmploymentStatus'])
                ->name('employment.status.assign');
            Route::delete('/remove', [UserEmploymentStatusController::class, 'removeEmploymentStatus'])
                ->name('employment.status.remove');
        });

        // Источники пользователя
        Route::prefix('sources')->group(function () {
            Route::get('/', [UserSourceController::class, 'getUserSources'])
                ->name('user.sources.get');
        });

        // Навыки пользователя
        Route::prefix('skills')->group(function () {
            Route::get('/', [UserSkillController::class, 'getUserSkills'])
                ->name('user.skills.get');
        });

        // Задачи пользователя
        Route::prefix('tasks')->group(function () {
            Route::get('/', [UserTaskController::class, 'index'])
                ->name('tasks.index');
            Route::get('/completed', [UserTaskController::class, 'completedTasks'])
                ->name('tasks.completed');
            Route::get('/in-progress', [UserTaskController::class, 'inProgressTasks'])
                ->name('tasks.in-progress');
            Route::get('/not-started', [UserTaskController::class, 'notStartedTasks'])
                ->name('tasks.not-started');
            Route::post('/{task}/progress', [UserTaskController::class, 'updateTaskProgress'])
                ->name('tasks.update-progress');
        });

        // Подписки на пользователей
        Route::prefix('follow')->group(function () {
            Route::post('/{userId}', [UserFollowController::class, 'follow'])
                ->name('follow.user');
            Route::delete('/{userId}', [UserFollowController::class, 'unfollow'])
                ->name('unfollow.user');
            Route::get('/followers', [UserFollowController::class, 'followers'])
                ->name('followers');
            Route::get('/following', [UserFollowController::class, 'following'])
                ->name('following');
        });

        ///v1/user/3/posts
        Route::prefix('{user}/posts')->group(function () {
           Route::get('/', [UserPostController::class, 'index'])->name('posts.index');
        });
    });

    // Уведомления
    Route::prefix('notifications')->group(function () {
        Route::get('/', [NotificationController::class, 'index'])
            ->name('notifications.index');
        Route::get('/unread', [NotificationController::class, 'unread'])
            ->name('notifications.unread');
        Route::patch('/{notification_id}/read', [NotificationController::class, 'markAsRead'])
            ->name('notifications.mark_read');
        Route::patch('/read-all', [NotificationController::class, 'markAllAsRead'])
            ->name('notifications.mark_all_read');
    });

    // Сообщения
    Route::prefix('messages')->group(function () {
        Route::get('/', [MessageController::class, 'index'])
            ->name('message.index');
        Route::get('/chats', [MessageController::class, 'getChats'])
            ->name('messages.chats');
        Route::get('/{user_id}', [MessageController::class, 'getMessages'])
            ->name('messages.get');
        Route::post('/{user_id}', [MessageController::class, 'sendMessage'])
            ->name('messages.send');
        Route::patch('/{user_id}/read', [MessageController::class, 'markAsRead'])
            ->name('messages.mark_read');
    });

    // Настройки аккаунта пользователя
    Route::prefix('user/account')->group(function () {
        Route::get('/', [UserAccountController::class, 'index'])
            ->name('user.account');
        Route::patch('/', [UserAccountController::class, 'update'])
            ->name('user.account.update');
        Route::delete('/', [UserAccountController::class, 'destroy'])
            ->name('user.account.destroy');
        Route::post('/restore', [UserAccountController::class, 'restore'])
            ->name('user.account.restore');
    });

    // Настройки пользователя
    Route::prefix('user/settings')->group(function () {
        Route::patch('/', [UserSettingsController::class, 'update'])
            ->name('user.settings.update');
    });

    Route::prefix('user/personalization')->group(function (){
       Route::post('/', [UserPersonalizationController::class, 'update']);
    });

    // Настройки уведомлений пользователя
    Route::prefix('user/notifications')->group(function () {
        Route::get('/', [UserNotificationSettingsController::class, 'index'])
            ->name('user.notifications.index');
        Route::patch('/', [UserNotificationSettingsController::class, 'update'])
            ->name('user.notifications.update');
    });

    // Статусы пользователя
    Route::prefix('user/statuses')->group(function () {
        Route::get('/', [UserStatusController::class, 'index'])->name('user.statuses.index'); // Получить все статусы
        Route::post('/assign', [UserStatusController::class, 'assign'])->name('user.statuses.assign'); // Привязать статус
        Route::delete('/detach', [UserStatusController::class, 'detach'])->name('user.statuses.detach'); // Отвязать статус
    });

    // Аватары
    Route::prefix('avatar')->group(function () {
        Route::post('/', [AvatarController::class, 'uploadAvatar'])
            ->name('avatars.upload');
        Route::get('/', [AvatarController::class, 'getUserAvatars'])
            ->name('avatars.get');
        Route::delete('/{avatarId}', [AvatarController::class, 'deleteAvatar'])
            ->name('avatars.delete');
        Route::post('/{avatarId}/set-active', [AvatarController::class, 'setActive'])
            ->name('avatars.delete');
    });

    // Обложки пользователя
    Route::prefix('cover')->group(function () {
        Route::get('/', [UserCoverController::class, 'show'])
            ->name('user.cover.show');
        Route::post('/', [UserCoverController::class, 'upload'])
            ->name('user.cover.upload');
        Route::delete('/', [UserCoverController::class, 'remove'])
            ->name('user.cover.remove');
    });

    Route::prefix('employment-statuses')->group(function () {
        Route::get('/', [EmploymentStatusController::class, 'index'])->name('employment-statuses.index');
    });

    // Медиафайлы
    Route::prefix('media')->group(function () {
        Route::post('/', [MediaController::class, 'store'])
            ->name('media.store');
        Route::get('/{id}', [MediaController::class, 'show'])
            ->name('media.show');
        Route::put('/{id}', [MediaController::class, 'update'])
            ->name('media.update');
        Route::delete('/{id}', [MediaController::class, 'destroy'])
            ->name('media.destroy');
    });

    Route::prefix('drafts')->group(function () {
        Route::get('/');
    });

    // Посты
    Route::prefix('posts')->group(function () {
        Route::get('/', [PostController::class, 'index'])
            ->name('posts.index');
        Route::post('/', [PostController::class, 'store'])
            ->name('posts.store');
        Route::get('/{id}', [PostController::class, 'show'])
            ->name('posts.show');
        Route::put('/{id}', [PostController::class, 'update'])
            ->name('posts.update');
        Route::delete('/{id}', [PostController::class, 'destroy'])
            ->name('posts.destroy');
        Route::post('/{id}/like', [PostController::class, 'toggleLike'])
            ->name('posts.like');
        Route::post('/{id}/repost', [PostController::class, 'repost'])
            ->name('posts.repost');
        Route::get('/{id}/download', [PostController::class, 'download'])
            ->name('post.download');

        // Статистика постов
        Route::prefix('statistics')->group(function () {
            Route::get('/{post}/summary', [PostStatisticController::class, 'getPostStatistics'])
                ->name('posts.statistics.post');
            Route::get('/summary', [PostStatisticController::class, 'summary'])
                ->name('posts.statistics.summary');
            Route::get('/recent', [PostStatisticController::class, 'recent'])
                ->name('posts.statistics.recent');
        });

        // Комментарии к постам
        Route::prefix('{post_id}/comments')->group(function () {
            Route::get('/', [CommentController::class, 'index'])
                ->name('comments.index');
            Route::post('/', [CommentController::class, 'store'])
                ->name('comments.store');
            Route::get('/{id}', [CommentController::class, 'show'])
                ->name('comments.show');
            Route::patch('/{id}', [CommentController::class, 'update'])
                ->name('comments.update');
            Route::delete('/{id}', [CommentController::class, 'destroy'])
                ->name('comments.destroy');
            Route::post('/{commentId}/react', [CommentController::class, 'react'])
                ->name('comments.react');
            Route::post('/{commentId}/report', [CommentController::class, 'report'])
                ->name('comments.report');
            Route::post('/{commentId}/repost', [CommentController::class, 'repost'])
                ->name('comments.repost');
        });
    });

    // Теги
    Route::prefix('tags')->group(function () {
        Route::get('/', [TagController::class, 'index'])
            ->name('tags.index');
        Route::get('/{tag}', [TagController::class, 'show'])
            ->name('tags.show');
        Route::post('/', [TagController::class, 'store'])
            ->name('tags.store');
        Route::put('/{tag}', [TagController::class, 'update'])
            ->name('tags.update');
    });

    Route::prefix('apps')->group(function () {
        Route::get('/', [AppController::class, 'index'])
            ->name('apps.index');
    });

    // Категории
    Route::prefix('categories')->group(function () {
        Route::get('/', [CategoryController::class, 'index'])
            ->name('categories.index');
    });

    // Навыки
    Route::prefix('skills')->group(function () {
        Route::get('/', [UserSkillController::class, 'index'])
            ->name('skills.index');
    });

    // Достижения
    Route::prefix('achievements')->group(function () {
        Route::get('/', [UserAchievementController::class, 'index'])
            ->name('achievements.index');
    });

    // Локации
    Route::prefix('locations')->group(function () {
        Route::get('/', [UserLocationController::class, 'index'])
            ->name('locations.index');
        Route::get('/{id}', [UserLocationController::class, 'show'])
            ->name('locations.show');
    });

    // Бейджи пользователя
    Route::prefix('user/badges')->group(function () {
        Route::get('/', [UserBadgeController::class, 'index'])
            ->name('user.badges.index');
        Route::get('/active', [UserBadgeController::class, 'getActiveBadge'])
            ->name('user.badges.active');
        Route::post('/active', [UserBadgeController::class, 'setActiveBadge'])
            ->name('user.badges.set-active');
        Route::get('/{id}', [UserBadgeController::class, 'show'])
            ->name('user.badges.show');
    });

    // Челленджи
    Route::prefix('challenges')->group(function () {
        Route::get('/', [ChallengeController::class, 'index'])
            ->name('challenges.index');
        Route::post('/', [ChallengeController::class, 'store'])
            ->name('challenges.store');
        Route::get('/active', [ChallengeController::class, 'getActiveChallenges'])
            ->name('challenges.active');
        Route::get('/user', [ChallengeController::class, 'getUserChallenges'])
            ->name('challenges.user');
        Route::get('/{id}', [ChallengeController::class, 'show'])
            ->name('challenges.show');
        Route::put('/{id}', [ChallengeController::class, 'update'])
            ->name('challenges.update');
        Route::delete('/{id}', [ChallengeController::class, 'destroy'])
            ->name('challenges.destroy');
        Route::post('/{id}/join', [ChallengeController::class, 'join'])
            ->name('challenges.join');
        Route::post('/{id}/leave', [ChallengeController::class, 'leave'])
            ->name('challenges.leave');
    });
});

// Маршруты для восстановления удаленного аккаунта (без аутентификации)
Route::prefix('account/recovery')->group(function () {
    Route::post('/request', [AccountRecoveryController::class, 'requestRecovery'])
        ->name('account.recovery.request');
    Route::post('/recover', [AccountRecoveryController::class, 'recoverAccount'])
        ->name('account.recovery.recover');
    Route::post('/check-status', [AccountRecoveryController::class, 'checkStatus'])
        ->name('account.recovery.check-status');
});
