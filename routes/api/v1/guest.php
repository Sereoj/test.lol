<?php

use App\Http\Controllers\Authentication\AccountRecoveryController;
use App\Http\Controllers\Authentication\AuthController;
use App\Http\Controllers\Authentication\EmailVerificationController;
use App\Http\Controllers\Authentication\PasswordResetController;
use App\Http\Controllers\Authentication\SocialiteController;
use App\Http\Controllers\ChallengeController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\ImageController;
use App\Http\Controllers\InitController;
use App\Http\Controllers\Posts\PostController;
use App\Http\Controllers\Posts\PostSearchController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\Stats\PlatformStatsController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\Users\UserLanguageController;
use App\Http\Controllers\Users\UserPostController;
use App\Http\Controllers\Users\UserProfileController;
use App\Http\Controllers\BadgeController;
use App\Http\Controllers\Help\HelpSearchController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Guest Routes (v1)
|--------------------------------------------------------------------------
| Маршруты, доступные всем пользователям (не требующие авторизации)
|
*/
// Маршруты, для которых явно указано middleware guest (только для неавторизованных)
Route::middleware('guest')->group(function () {
    // Инициализация приложения
    Route::get('/init', [InitController::class, 'init'])
        ->name('init.public');

    Route::get('/sitemap', [SitemapController::class, 'index'])
        ->name('sitemap');

    // Аутентификация
    Route::prefix('auth')->group(function () {
        Route::get('redirect/{provider}', [SocialiteController::class, 'redirectToProvider'])
            ->name('auth.redirect');
        Route::get('callback/{provider}', [SocialiteController::class, 'handleProviderCallback'])
            ->name('auth.callback');

        Route::post('/register', [AuthController::class, 'register'])
            ->name('register.public');
        Route::post('/login', [AuthController::class, 'login'])
            ->name('login.public');


        Route::post('/forgot-password', [PasswordResetController::class, 'sendPasswordResetEmail'])
            ->name('forgot-password.email');
        Route::post('/reset-password', [PasswordResetController::class, 'resetPassword'])
            ->name('reset.password');

        // Управление токенами и подтверждение email
        Route::post('/refresh-token', [AuthController::class, 'refreshToken'])
            ->name('refresh-token');
    });

    Route::post('/send-verification-code', [EmailVerificationController::class, 'sendVerificationCode'])
        ->name('send.verification.code');
    Route::post('/verify-email', [EmailVerificationController::class, 'verifyEmail'])
        ->name('verify.email');

    Route::prefix('user')->group(function () {
        Route::prefix('{user}/posts')->group(function () {
            Route::get('/', [UserPostController::class, 'index'])->name('user.posts.index.public');
        });
    });
});

// Маршруты, доступные как гостям, так и авторизованным пользователям
// Версия приложения
Route::get('/version', function () {
    $composerJson = json_decode(file_get_contents(base_path('composer.json')), true);
    $packageJson = json_decode(file_get_contents(base_path('package.json')), true);

    return response()->json([
        'success' => true,
        'data' => [
            'version' => $composerJson['version'] ?? 'unknown',
            'name' => $composerJson['name'] ?? 'unknown',
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'environment' => app()->environment(),
            'package_version' => $packageJson['version'] ?? 'unknown',
        ]
    ]);
})->name('version.public');

// Установка языка
Route::get('/languages', [UserLanguageController::class, 'index'])
    ->name('lang.public');
Route::post('/languages', [UserLanguageController::class, 'switchLanguage'])
    ->name('set.language');

Route::prefix('storage')->group(function () {
   Route::get('/originals/{filename}', [ImageController::class, 'getOriginal'])
       ->name('storage.original');
});

//http://test/public/api/v1/search/tags?query=&page=1&per_page=12

// Поиск постов
Route::prefix('search')->group(function () {
    Route::get('/', [PostSearchController::class, 'search'])
        ->name('posts.search.public');
    Route::get('/tags', [PostSearchController::class, 'searchTags'])
        ->name('posts.search.tags');
    Route::get('/posts', [PostSearchController::class, 'searchPosts'])
        ->name('posts.search.posts');
    Route::get('/users', [PostSearchController::class, 'searchUsers'])
        ->name('posts.search.users');
    Route::get('/suggest', [PostSearchController::class, 'suggest'])
        ->name('posts.suggest.public');
});

// Маршруты для восстановления удаленного аккаунта (без аутентификации)
Route::prefix('account/recovery')->group(function () {
    Route::post('/request', [AccountRecoveryController::class, 'requestRecovery'])
        ->name('account.recovery.request');
    Route::post('/recover', [AccountRecoveryController::class, 'recoverAccount'])
        ->name('account.recovery.recover');
});

// Публичные маршруты для постов
Route::prefix('posts')->group(function () {
    Route::get('/', [PostController::class, 'index'])
        ->name('posts.index.public');
    Route::get('/{id}', [PostController::class, 'show'])
        ->name('posts.show.public');

    // Комментарии к постам (только чтение)
    Route::get('{post_id}/comments', [CommentController::class, 'index'])
        ->name('comments.index.public');
});

// Публичный профиль
Route::prefix('profile')->group(function () {
    Route::get('/{slug}', [UserProfileController::class, 'show'])
        ->name('profile.show.public');
});

// Публичные маршруты для челленджей
Route::prefix('challenges')->group(function () {
    Route::get('/', [ChallengeController::class, 'index'])
        ->name('challenges.index.public');
    Route::get('/active', [ChallengeController::class, 'getActiveChallenges'])
        ->name('challenges.active.public');
    Route::get('/{id}', [ChallengeController::class, 'show'])
        ->name('challenges.show.public');
});

// Публичные маршруты для бейджей
Route::prefix('badges')->group(function () {
    Route::get('/', [BadgeController::class, 'index'])
        ->name('badges.index.public');
    Route::get('/{id}', [BadgeController::class, 'show'])
        ->name('badges.show.public');
});

// Статистика платформы
Route::prefix('stats')->group(function () {
    Route::get('/platform', [PlatformStatsController::class, 'index'])
        ->name('stats.platform.public');
});

// Поиск по базе знаний
Route::prefix('help')->group(function () {
    Route::get('/search', [HelpSearchController::class, 'search'])
        ->name('help.search.public');
});
