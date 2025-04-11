<?php

use App\Http\Controllers\Admin\AnalyticsController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\MediaController;
use App\Http\Controllers\Admin\PostController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\TagController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\UserReportController;
use App\Http\Controllers\Admin\ContentReportController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Admin Routes (v1)
|--------------------------------------------------------------------------
| Маршруты для администраторов системы
| Все эти маршруты защищены middleware auth:api и требуют роли администратора
|
*/

Route::middleware(['auth:api', 'role:admin'])->prefix('admin')->group(function () {
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
            ->name('admin.users.index');
        Route::get('/{id}', [UserController::class, 'show'])
            ->name('admin.users.show');
        Route::post('/', [UserController::class, 'store'])
            ->name('admin.users.store');
        Route::patch('/{id}', [UserController::class, 'update'])
            ->name('admin.users.update');
        Route::delete('/{id}', [UserController::class, 'destroy'])
            ->name('admin.users.destroy');
        Route::patch('/{id}/ban', [UserController::class, 'ban'])
            ->name('admin.users.ban');
        Route::patch('/{id}/unban', [UserController::class, 'unban'])
            ->name('admin.users.unban');
        Route::patch('/{id}/verify', [UserController::class, 'verify'])
            ->name('admin.users.verify');
        Route::patch('/{id}/unverify', [UserController::class, 'unverify'])
            ->name('admin.users.unverify');
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
        Route::get('/', [CategoryController::class, 'index'])
            ->name('admin.categories.index');
        Route::get('/{id}', [CategoryController::class, 'show'])
            ->name('admin.categories.show');
        Route::post('/', [CategoryController::class, 'store'])
            ->name('admin.categories.store');
        Route::patch('/{id}', [CategoryController::class, 'update'])
            ->name('admin.categories.update');
        Route::delete('/{id}', [CategoryController::class, 'destroy'])
            ->name('admin.categories.destroy');
    });

    // Управление тегами
    Route::prefix('tags')->group(function () {
        Route::get('/', [TagController::class, 'index'])
            ->name('admin.tags.index');
        Route::get('/{id}', [TagController::class, 'show'])
            ->name('admin.tags.show');
        Route::post('/', [TagController::class, 'store'])
            ->name('admin.tags.store');
        Route::patch('/{id}', [TagController::class, 'update'])
            ->name('admin.tags.update');
        Route::delete('/{id}', [TagController::class, 'destroy'])
            ->name('admin.tags.destroy');
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