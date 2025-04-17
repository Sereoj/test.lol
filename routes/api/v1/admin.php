<?php

use App\Http\Controllers\Admin\AnalyticsController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\MediaController;
use App\Http\Controllers\Admin\PostController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\TagController;
use App\Http\Controllers\Admin\UserReportController;
use App\Http\Controllers\Admin\ContentReportController;
use App\Http\Controllers\Apps\AppController;
use App\Http\Controllers\BadgeController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\EmploymentStatusController;
use App\Http\Controllers\SourceController;
use App\Http\Controllers\Statuses\StatusController;
use App\Http\Controllers\Users\UserController;
use App\Http\Controllers\Users\UserLevelController;
use App\Http\Controllers\Users\UserLocationController;
use App\Http\Controllers\Users\UserSkillController;
use App\Http\Controllers\Users\UserBadgeController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Admin Routes (v1)
|--------------------------------------------------------------------------
| Маршруты для администраторов системы
| Все эти маршруты защищены middleware auth:api и требуют роли администратора
|
*/

Route::middleware(['auth:api', 'role:admin'])->group(function () {
    // Дашборд
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('admin.dashboard');

    // Аналитика
    Route::prefix('analytics')->group(function () {
        Route::get('/users', [AnalyticsController::class, 'users'])
            ->name('admin.analytics.users');
        Route::get('/posts', [AnalyticsController::class, 'posts'])
            ->name('admin.analytics.posts');
        Route::get('/revenue', [AnalyticsController::class, 'revenue'])
            ->name('admin.analytics.revenue');
        Route::get('/engagement', [AnalyticsController::class, 'engagement'])
            ->name('admin.analytics.engagement');
    });

    // Управление пользователями
    Route::prefix('users')->group(function () {
        Route::get('/', [UserController::class, 'index'])
            ->name('users.index'); // для администраторов
        Route::get('/{id}', [UserController::class, 'show'])
            ->name('users.show'); // для администраторов
        Route::post('/', [UserController::class, 'store'])
            ->name('users.store'); // для администраторов
        Route::put('/{id}', [UserController::class, 'update'])
            ->name('users.update'); // для администраторов
        Route::delete('/{id}', [UserController::class, 'destroy'])
            ->name('users.destroy'); // для администраторов
        Route::put('/{userId}/change-role', [UserController::class, 'changeRole'])
            ->name('users.change-role'); // для администраторов
    });

    // Управление источниками
    Route::prefix('sources')->group(function () {
        Route::get('/', [SourceController::class, 'index'])
            ->name('sources.index'); // для администраторов
        Route::post('/', [SourceController::class, 'store'])
            ->name('sources.store'); // для администраторов
        Route::get('/{source}', [SourceController::class, 'show'])
            ->name('sources.index'); // для администраторов
        Route::put('/{id}', [SourceController::class, 'update'])
            ->name('sources.update'); // для администраторов
        Route::delete('/{id}', [SourceController::class, 'destroy'])
            ->name('sources.destroy'); // для администраторов
    });

    // Управление навыками
    Route::prefix('skills')->group(function () {
        Route::middleware(['role:admin'])->group(function () {
            Route::post('/', [UserSkillController::class, 'store'])->name('skills.store'); // для администраторов
            Route::get('/{id}', [UserSkillController::class, 'show'])->name('skills.show'); // для администраторов
            Route::put('/{id}', [UserSkillController::class, 'update'])->name('skills.update'); // для администраторов
            Route::delete('/{id}', [UserSkillController::class, 'destroy'])->name('skills.destroy'); // для администраторов
        });
    });

    // Управление локациями
    Route::prefix('locations')->group(function () {
        Route::post('/', [UserLocationController::class, 'store'])->name('locations.store');
        Route::put('/{id}', [UserLocationController::class, 'update'])->name('locations.update');
        Route::delete('/{id}', [UserLocationController::class, 'destroy'])->name('locations.destroy');
    });

    // Управление наградами
    Route::prefix('badges')->group(function () {
        Route::get('/', [BadgeController::class, 'index'])
            ->name('admin.badges.index');
        Route::get('/{id}', [BadgeController::class, 'show'])
            ->name('admin.badges.show');
        Route::post('/', [BadgeController::class, 'store'])
            ->name('admin.badges.store');
        Route::put('/{id}', [BadgeController::class, 'update'])
            ->name('admin.badges.update');
        Route::delete('/{id}', [BadgeController::class, 'destroy'])
            ->name('admin.badges.destroy');
    });

    // Управление пользовательскими наградами
    Route::prefix('user-badges')->group(function () {
        Route::get('/', [UserBadgeController::class, 'index'])
            ->name('admin.user-badges.index');
        Route::get('/{id}', [UserBadgeController::class, 'show'])
            ->name('admin.user-badges.show');
        Route::post('/', [UserBadgeController::class, 'store'])
            ->name('admin.user-badges.store');
        Route::put('/{id}', [UserBadgeController::class, 'update'])
            ->name('admin.user-badges.update');
        Route::delete('/{id}', [UserBadgeController::class, 'destroy'])
            ->name('admin.user-badges.destroy');
    });

    // Управление статусами занятости
    Route::prefix('employment-statuses')->group(function () {
        Route::get('/{id}', [EmploymentStatusController::class, 'show'])
            ->name('employment-statuses.show');
        Route::post('/', [EmploymentStatusController::class, 'store'])
            ->name('employment-statuses.store');
        Route::put('/{id}', [EmploymentStatusController::class, 'update'])
            ->name('employment-statuses.update');
        Route::delete('/{id}', [EmploymentStatusController::class, 'destroy'])
            ->name('employment-statuses.destroy');
    });

    // Управление ролями
    Route::prefix('roles')->group(function () {
        Route::get('/', [RoleController::class, 'index'])
            ->name('admin.roles.index');
        Route::get('/{id}', [RoleController::class, 'show'])
            ->name('admin.roles.show');
        Route::post('/', [RoleController::class, 'store'])
            ->name('admin.roles.store');
        Route::patch('/{id}', [RoleController::class, 'update'])
            ->name('admin.roles.update');
        Route::delete('/{id}', [RoleController::class, 'destroy'])
            ->name('admin.roles.destroy');
        Route::post('/{role_id}/assign/{user_id}', [RoleController::class, 'assignRole'])
            ->name('admin.roles.assign');
        Route::delete('/{role_id}/remove/{user_id}', [RoleController::class, 'removeRole'])
            ->name('admin.roles.remove');
    });

    // Управление постами
    Route::prefix('posts')->group(function () {
        Route::get('/', [PostController::class, 'index'])
            ->name('admin.posts.index');
        Route::get('/{id}', [PostController::class, 'show'])
            ->name('admin.posts.show');
        Route::post('/', [PostController::class, 'store'])
            ->name('admin.posts.store');
        Route::patch('/{id}', [PostController::class, 'update'])
            ->name('admin.posts.update');
        Route::delete('/{id}', [PostController::class, 'destroy'])
            ->name('admin.posts.destroy');
        Route::patch('/{id}/approve', [PostController::class, 'approve'])
            ->name('admin.posts.approve');
        Route::patch('/{id}/reject', [PostController::class, 'reject'])
            ->name('admin.posts.reject');
        Route::patch('/{id}/feature', [PostController::class, 'feature'])
            ->name('admin.posts.feature');
        Route::patch('/{id}/unfeature', [PostController::class, 'unfeature'])
            ->name('admin.posts.unfeature');
    });

    // Управление категориями
    Route::prefix('categories')->group(function () {
        Route::post('/', [CategoryController::class, 'store'])
            ->name('categories.store'); // для администраторов
        Route::put('/{category}', [CategoryController::class, 'update'])
            ->name('categories.update'); // для администраторов
        Route::delete('/{category}', [CategoryController::class, 'destroy'])
            ->name('categories.destroy'); // для администраторов
    });

    // Управление статусами
    Route::prefix('statuses')->group(function () {
        Route::get('/', [StatusController::class, 'index'])
            ->name('admin.statuses.index'); // Получить все статусы
        Route::post('/', [StatusController::class, 'store'])
            ->name('admin.statuses.store'); // Создать новый статус
        Route::get('/{id}', [StatusController::class, 'show'])
            ->name('admin.statuses.show'); // Получить статус по ID
        Route::put('/{id}', [StatusController::class, 'update'])
            ->name('admin.statuses.update'); // Обновить статус
        Route::delete('/{id}', [StatusController::class, 'destroy'])
            ->name('admin.statuses.destroy'); // Удалить статус
    });

    Route::prefix('apps')->group(function () {
        Route::get('/', [AppController::class, 'index'])
            ->name('admin.apps.index'); // для администраторов
        Route::post('/', [AppController::class, 'store'])
            ->name('admin.apps.store'); // для администраторов
        Route::get('/{id}', [AppController::class, 'show'])
            ->name('admin.apps.show'); // для администраторов
        Route::put('/{id}', [AppController::class, 'update'])
            ->name('admin.apps.update'); // для администраторов
        Route::delete('/{id}', [AppController::class, 'destroy'])
            ->name('admin.apps.destroy'); // для администраторов
    });

    Route::prefix('levels')->group(function () {
        Route::get('/', [UserLevelController::class, 'index'])
            ->name('admin.levels.index'); // для администраторов
        Route::post('/', [UserLevelController::class, 'store'])
            ->name('admin.levels.store'); // для администраторов
    });

    // Управление тегами
    Route::prefix('tags')->group(function () {
        Route::delete('/{tag}', [TagController::class, 'destroy'])
            ->name('tags.destroy'); // для администраторов
    });

    // Управление медиа файлами
    Route::prefix('media')->group(function () {
        Route::get('/', [MediaController::class, 'index'])
            ->name('admin.media.index');
        Route::get('/{id}', [MediaController::class, 'show'])
            ->name('admin.media.show');
        Route::delete('/{id}', [MediaController::class, 'destroy'])
            ->name('admin.media.destroy');
    });

    // Управление настройками системы
    Route::prefix('settings')->group(function () {
        Route::get('/', [SettingsController::class, 'index'])
            ->name('admin.settings.index');
        Route::patch('/', [SettingsController::class, 'update'])
            ->name('admin.settings.update');
        Route::post('/cache/clear', [SettingsController::class, 'clearCache'])
            ->name('admin.settings.clear-cache');
        Route::post('/maintenance/{status}', [SettingsController::class, 'setMaintenanceMode'])
            ->name('admin.settings.maintenance');
    });

    // Жалобы на пользователей
    Route::prefix('reports/users')->group(function () {
        Route::get('/', [UserReportController::class, 'index'])
            ->name('admin.reports.users.index');
        Route::get('/{id}', [UserReportController::class, 'show'])
            ->name('admin.reports.users.show');
        Route::patch('/{id}/resolve', [UserReportController::class, 'resolve'])
            ->name('admin.reports.users.resolve');
        Route::patch('/{id}/dismiss', [UserReportController::class, 'dismiss'])
            ->name('admin.reports.users.dismiss');
    });

    // Жалобы на контент
    Route::prefix('reports/content')->group(function () {
        Route::get('/', [ContentReportController::class, 'index'])
            ->name('admin.reports.content.index');
        Route::get('/{id}', [ContentReportController::class, 'show'])
            ->name('admin.reports.content.show');
        Route::patch('/{id}/resolve', [ContentReportController::class, 'resolve'])
            ->name('admin.reports.content.resolve');
        Route::patch('/{id}/dismiss', [ContentReportController::class, 'dismiss'])
            ->name('admin.reports.content.dismiss');
    });
});
