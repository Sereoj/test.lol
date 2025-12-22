<?php

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
            ->name('sources.show'); // для администраторов
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
});
