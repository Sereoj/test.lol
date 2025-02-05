<?php

use App\Http\Controllers\AppController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\EmailVerificationController;
use App\Http\Controllers\Auth\SocialiteController;
use App\Http\Controllers\Auth\StepController;
use App\Http\Controllers\BadgeController;
use App\Http\Controllers\BalanceController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\EmploymentStatusController;
use App\Http\Controllers\LevelController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\PostSearchController;
use App\Http\Controllers\PostStatisticController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SkillController;
use App\Http\Controllers\SourceController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\User\AvatarController;
use App\Http\Controllers\User\UserAchievementController;
use App\Http\Controllers\User\UserBadgeController;
use App\Http\Controllers\User\UserController;
use App\Http\Controllers\User\UserEmploymentStatusController;
use App\Http\Controllers\User\UserFollowController;
use App\Http\Controllers\User\UserLanguageController;
use App\Http\Controllers\User\UserProfileController;
use App\Http\Controllers\User\UserSourceController;
use App\Http\Controllers\User\UserTaskController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|---------------------------------------------------------------------------
| API Routes
|---------------------------------------------------------------------------
| Here is where you can register API routes for your application.
| These routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

/*Route::post('/oauth/token', [AccessTokenController::class, 'issueToken'])->name('passport.token');
Route::get('/oauth/authorize', [AuthorizationController::class, 'authorize'])->name('passport.authorizations.authorize');
Route::post('/oauth/token/refresh', [TransientTokenController::class, 'refresh'])->name('passport.token.refresh');
Route::get('/oauth/scopes', [ScopeController::class, 'all'])->name('passport.scopes.all');
Route::get('/oauth/clients', [ClientController::class, 'forUser'])->name('passport.clients.forUser');
Route::post('/oauth/clients', [ClientController::class, 'store'])->name('passport.clients.store');
Route::delete('/oauth/clients/{client_id}', [ClientController::class, 'destroy'])->name('passport.clients.destroy');
Route::post('/oauth/personal-access-tokens', [PersonalAccessTokenController::class, 'store'])->name('passport.personal_tokens.store');
Route::delete('/oauth/personal-access-tokens/{token_id}', [PersonalAccessTokenController::class, 'destroy'])->name('passport.personal_tokens.destroy');*/

// Public routes
Route::post('/language', [UserLanguageController::class, 'setLanguage'])->middleware('guest')->name('set.language'); // для гостей или авторизированных
Route::post('/register', [AuthController::class, 'register'])->middleware('guest')->name('register'); // только для гостей
Route::post('/login', [AuthController::class, 'login'])->middleware('guest')->name('login'); // только для гостей
Route::post('/refresh-token', [AuthController::class, 'refreshToken'])->name('refresh-token'); // для авторизированных
Route::post('/send-verification-code', [EmailVerificationController::class, 'sendVerificationCode'])->middleware('guest')->name('send.verification.code'); // для гостей
Route::post('/verify-email', [EmailVerificationController::class, 'verifyEmail'])->middleware('guest')->name('verify.email'); // для гостей
Route::post('/password/reset/email', [PasswordResetController::class, 'sendPasswordResetEmail'])->middleware('guest')->name('password.reset.email'); // только для гостей
Route::post('/password/reset', [PasswordResetController::class, 'resetPassword'])->middleware('guest')->name('password.reset'); // только для гостей

// Socialite routes
Route::prefix('auth')->middleware('guest')->group(function () {
    Route::get('redirect/{provider}', [SocialiteController::class, 'redirectToProvider'])->name('auth.redirect'); // для гостей
    Route::get('callback/{provider}', [SocialiteController::class, 'handleProviderCallback'])->name('auth.callback'); // для гостей
});

// Public posts route
Route::get('/posts', [PostController::class, 'index'])->name('posts.index'); // для гостей или авторизированных
Route::get('/search', [PostSearchController::class, 'search'])->name('posts.search'); // для гостей
Route::get('/search/suggest', [PostSearchController::class, 'suggest'])->name('posts.suggest'); // для гостей

// Authenticated routes
Route::middleware('auth:api')->group(function () {
    // Auth routes
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout'); // для авторизированных

    Route::prefix('auth/step')->group(function () {
        Route::post('/one', [StepController::class, 'one'])->name('step.one'); // для авторизированных
        Route::post('/two', [StepController::class, 'two'])->name('step.two'); // для авторизированных
        Route::post('/three', [StepController::class, 'three'])->name('step.three'); // для авторизированных
    });

    Route::prefix('user')->group(function () {
        Route::get('/', [AuthController::class, 'user'])->name('user'); // для авторизированных

        // Баланс
        Route::get('/balance', [BalanceController::class, 'getBalance']);
        Route::post('/balance/topup', [BalanceController::class, 'topUpBalance']);
        Route::post('/balance/withdraw', [BalanceController::class, 'withdrawBalance']);
        Route::post('/balance/transfer', [BalanceController::class, 'transferBalance']);
        // Транзакции
        Route::get('/transactions', [TransactionController::class, 'getTransactions']);
        // Покупки
        Route::post('/posts/{postId}/purchase', [PurchaseController::class, 'purchasePost']);

        Route::get('/subscriptions/active', [SubscriptionController::class, 'getActiveSubscription']);
        Route::post('/subscriptions', [SubscriptionController::class, 'createSubscription']);
        Route::post('/subscriptions/{subscriptionId}/extend', [SubscriptionController::class, 'extendSubscription']);

        Route::prefix('profile')->group(function () {
            Route::get('/', [UserProfileController::class, 'show'])->name('profile.show');
            Route::put('/', [UserProfileController::class, 'update'])->name('profile.update');
        });

        Route::prefix('employment-status')->group(function () {
            Route::post('/assign', [UserEmploymentStatusController::class, 'assignEmploymentStatus'])->name('employment.status.assign');
            Route::delete('/remove', [UserEmploymentStatusController::class, 'removeEmploymentStatus'])->name('employment.status.remove');
        });

        // User sources routes
        Route::prefix('sources')->group(function () {
            Route::post('/', [UserSourceController::class, 'addSource'])->name('user.sources.add'); // для администраторов
            Route::delete('/', [UserSourceController::class, 'removeSource'])->name('user.sources.remove'); // для администраторов
            Route::get('/', [UserSourceController::class, 'getUserSources'])->name('user.sources.get'); // для авторизированных
        });

        // User skills routes
        Route::prefix('skills')->group(function () {
            Route::post('/', [SkillController::class, 'addSkill'])->middleware('role:admin')->name('user.skills.add'); // для администраторов
            Route::delete('/', [SkillController::class, 'removeSkill'])->middleware('role:admin')->name('user.skills.remove'); // для администраторов
            Route::get('/', [SkillController::class, 'getUserSkills'])->name('user.skills.get'); // для авторизированных
        });

        Route::prefix('badges')->group(function () {
            Route::post('/', [UserBadgeController::class, 'store'])->name('user-badges.store'); // для авторизированных
            Route::get('/active', [UserBadgeController::class, 'getActiveBadge'])->name('user-badges.get-active');
            Route::post('/active', [UserBadgeController::class, 'setActiveBadge'])->name('user-badges.set-active'); // для авторизированных
            Route::get('/', [UserBadgeController::class, 'index'])->name('user-badges.index'); // для авторизированных
            Route::get('/{id}', [UserBadgeController::class, 'show'])->name('user-badges.show'); // для авторизированных
            Route::put('/{id}', [UserBadgeController::class, 'update'])->name('user-badges.update'); // для авторизированных
            Route::delete('/{id}', [UserBadgeController::class, 'destroy'])->name('user-badges.destroy'); // для авторизированных
        });

        Route::prefix('tasks')->group(function () {
            Route::get('/', [UserTaskController::class, 'index'])->name('tasks.index');
            Route::get('/completed', [UserTaskController::class, 'completedTasks'])->name('tasks.completed');
            Route::get('/in-progress', [UserTaskController::class, 'inProgressTasks'])->name('tasks.in-progress');
            Route::get('/not-started', [UserTaskController::class, 'notStartedTasks'])->name('tasks.not-started');
            Route::post('/{task}/progress', [UserTaskController::class, 'updateTaskProgress'])->name('tasks.update-progress');
        });

        Route::prefix('follow')->group(function () {
            Route::post('/{userId}', [UserFollowController::class, 'follow'])->name('follow.user');
            Route::delete('/{userId}', [UserFollowController::class, 'unfollow'])->name('unfollow.user');
            Route::get('/followers', [UserFollowController::class, 'followers'])->name('followers');
            Route::get('/following', [UserFollowController::class, 'following'])->name('following');
        });
    });

    // Avatars routes
    Route::prefix('avatars')->group(function () {
        Route::post('/', [AvatarController::class, 'uploadAvatar'])->name('avatars.upload'); // для авторизированных
        Route::get('/', [AvatarController::class, 'getUserAvatars'])->name('avatars.get'); // для авторизированных
        Route::delete('/{avatarId}', [AvatarController::class, 'deleteAvatar'])->name('avatars.delete'); // для авторизированных
    });

    // Media routes
    Route::prefix('media')->group(function () {
        Route::post('/', [MediaController::class, 'store'])->name('media.store'); // для авторизированных
        Route::get('/{id}', [MediaController::class, 'show'])->name('media.show'); // для авторизированных
        Route::put('/{id}', [MediaController::class, 'update'])->name('media.update'); // для авторизированных
        Route::delete('/{id}', [MediaController::class, 'destroy'])->name('media.destroy'); // для авторизированных
    });

    // Posts routes
    Route::prefix('posts')->group(function () {
        Route::get('/', [PostController::class, 'index'])->name('posts.index'); // для авторизированных или гостей
        Route::post('/', [PostController::class, 'store'])->name('posts.store'); // для авторизированных
        Route::get('/{id}', [PostController::class, 'show'])->name('posts.show'); // для авторизированных или гостей
        Route::put('/{id}', [PostController::class, 'update'])->name('posts.update'); // для авторизированных
        Route::delete('/{id}', [PostController::class, 'destroy'])->name('posts.destroy'); // для авторизированных
        Route::post('/{id}/like', [PostController::class, 'toggleLike'])->name('posts.like'); // для авторизированных
        Route::post('/{id}/repost', [PostController::class, 'repost'])->name('posts.repost'); // для авторизированных
        Route::get('/{id}/download', [PostController::class, 'download'])->name('post.download'); // для авторизированных

        // Post statistics routes
        Route::prefix('statistics')->group(function () {
            Route::get('/summary', [PostStatisticController::class, 'summary'])->name('posts.statistics.summary'); // для авторизированных
            Route::get('/recent', [PostStatisticController::class, 'recent'])->name('posts.statistics.recent'); // для авторизированных
        });

        // Comments routes
        Route::prefix('{post_id}/comments')->group(function () {
            Route::get('/', [CommentController::class, 'index'])->name('comments.index'); // для гостей или авторизированных
            Route::post('/', [CommentController::class, 'store'])->name('comments.store'); // для авторизированных
            Route::get('/{id}', [CommentController::class, 'show'])->name('comments.show'); // для авторизированных
            Route::put('/{id}', [CommentController::class, 'update'])->name('comments.update'); // для авторизированных
            Route::delete('/{id}', [CommentController::class, 'destroy'])->name('comments.destroy'); // для авторизированных
            Route::post('/{commentId}/react', [CommentController::class, 'react'])->name('comments.react'); // для авторизированных
            Route::post('/{commentId}/report', [CommentController::class, 'report'])->name('comments.report'); // для авторизированных
            Route::post('/{commentId}/repost', [CommentController::class, 'repost'])->name('comments.repost'); // для авторизированных
        });
    });

    // Tags routes
    Route::prefix('tags')->group(function () {
        Route::get('/', [TagController::class, 'index'])->name('tags.index'); // для гостей или авторизированных
        Route::get('/{tag}', [TagController::class, 'show'])->name('tags.show'); // авторизированных

        Route::middleware('role:admin')->group(function () {
            Route::post('/tags', [TagController::class, 'store'])->name('tags.store'); // для администраторов
            Route::put('/{tag}', [TagController::class, 'update'])->name('tags.update'); // для администраторов
            Route::delete('/{tag}', [TagController::class, 'destroy'])->name('tags.destroy'); // для администраторов
        });
    });

    // Categories routes
    Route::prefix('categories')->group(function () {
        Route::get('/', [CategoryController::class, 'index'])->name('categories.index'); // для гостей или авторизированных

        Route::middleware('role:admin')->group(function () {
            Route::post('/', [CategoryController::class, 'store'])->name('categories.store'); // для администраторов
            Route::put('/{category}', [CategoryController::class, 'update'])->name('categories.update'); // для администраторов
            Route::delete('/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy'); // для администраторов
        });
    });

    // Users routes
    Route::prefix('users')->group(function () {
        Route::middleware(['role:admin'])->group(function () {
            Route::get('/', [UserController::class, 'index'])->name('users.index'); // для администраторов
            Route::get('/{id}', [UserController::class, 'show'])->name('users.show'); // для администраторов
            Route::post('/', [UserController::class, 'store'])->name('users.store'); // для администраторов
            Route::put('/{id}', [UserController::class, 'update'])->name('users.update'); // для администраторов
            Route::delete('/{id}', [UserController::class, 'destroy'])->name('users.destroy'); // для администраторов
            Route::put('/{userId}/change-role', [UserController::class, 'changeRole'])->name('users.change-role'); // для администраторов
        });
    });

    // Sources routes
    Route::prefix('sources')->middleware('role:admin')->group(function () {
        Route::get('/', [SourceController::class, 'index'])->name('sources.index'); // для администраторов
        Route::post('/', [SourceController::class, 'store'])->name('sources.store'); // для администраторов
        Route::get('/{source}', [SourceController::class, 'show'])->name('sources.index'); // для администраторов
        Route::put('/{id}', [SourceController::class, 'update'])->name('sources.update'); // для администраторов
        Route::delete('/{id}', [SourceController::class, 'destroy'])->name('sources.destroy'); // для администраторов
    });

    Route::prefix('skills')->group(function () {
        Route::get('/', [SkillController::class, 'index'])->name('skills.index'); // авторизированных

        Route::middleware(['role:admin'])->group(function () {
            Route::post('/', [SkillController::class, 'store'])->name('skills.store'); // для администраторов
            Route::get('/{id}', [SkillController::class, 'show'])->name('skills.show'); // для администраторов
            Route::put('/{id}', [SkillController::class, 'update'])->name('skills.update'); // для администраторов
            Route::delete('/{id}', [SkillController::class, 'destroy'])->name('skills.destroy'); // для администраторов
        });
    });

    Route::prefix('apps')->middleware('role:admin')->group(function () {
        Route::get('/', [AppController::class, 'index'])->name('apps.index'); // для администраторов
        Route::post('/', [AppController::class, 'store'])->name('apps.store'); // для администраторов
        Route::get('/{id}', [AppController::class, 'show'])->name('apps.show'); // для администраторов
        Route::put('/{id}', [AppController::class, 'update'])->name('apps.update'); // для администраторов
        Route::delete('/{id}', [AppController::class, 'destroy'])->name('apps.destroy'); // для администраторов
    });

    Route::prefix('levels')->middleware('role:admin')->group(function () {
        Route::get('/', [LevelController::class, 'index'])->name('levels.index'); // для администраторов
        Route::post('/', [LevelController::class, 'store'])->name('levels.store'); // для администраторов
    });

    Route::prefix('roles')->middleware('role:admin')->group(function () {
        Route::get('/{role}', [RoleController::class, 'show'])->name('roles.show');  // авторизированных
        Route::resource('/', RoleController::class)->except(['show']);
    });

    Route::prefix('achievements')->group(function () {
        Route::get('/', [UserAchievementController::class, 'index'])->name('achievements.index'); // для авторизированных
        Route::post('/', [UserAchievementController::class, 'store'])->middleware('role:admin')->name('achievements.store'); // для администраторов
        Route::delete('/{achievement}', [UserAchievementController::class, 'destroy'])->middleware('role:admin')->name('achievements.destroy'); // для администраторов
    });

    Route::prefix('locations')->group(function () {
        Route::get('/', [LocationController::class, 'index'])->name('locations.index');
        Route::get('/{id}', [LocationController::class, 'show'])->name('locations.show');

        Route::middleware(['role:admin'])->group(function () {
            Route::post('/', [LocationController::class, 'store'])->name('locations.store');
            Route::put('/{id}', [LocationController::class, 'update'])->name('locations.update');
            Route::delete('/{id}', [LocationController::class, 'destroy'])->name('locations.destroy');
        });
    });

    Route::prefix('badges')->group(function () {
        Route::get('/', [BadgeController::class, 'index'])->name('badges.index');

        Route::middleware(['role:admin'])->group(function () {
            Route::get('/{id}', [BadgeController::class, 'show'])->name('badges.show');
            Route::post('', [BadgeController::class, 'store'])->name('badges.store');
            Route::put('/{id}', [BadgeController::class, 'update'])->name('badges.update');
            Route::delete('/{id}', [BadgeController::class, 'destroy'])->name('badges.destroy');
        });
    });

    Route::prefix('employment-statuses')->group(function () {
        Route::get('/', [EmploymentStatusController::class, 'index'])->name('employment-statuses.index');

        Route::middleware(['role:admin'])->group(function () {
            Route::get('/{id}', [EmploymentStatusController::class, 'show'])->name('employment-statuses.show');
            Route::post('/', [EmploymentStatusController::class, 'store'])->name('employment-statuses.store');
            Route::put('/{id}', [EmploymentStatusController::class, 'update'])->name('employment-statuses.update');
            Route::delete('/{id}', [EmploymentStatusController::class, 'destroy'])->name('employment-statuses.destroy');
        });
    });
});

Route::fallback(function (Request $request) {
    $method = $request->getMethod();
    $uri = $request->getRequestUri();

    return response()->json([
        'error' => 'Route not found or method not supported',
        'supported_methods' => ['GET', 'HEAD', 'POST', 'PUT', 'DELETE', 'OPTIONS', 'PATCH'],
        'message' => "The request to route {$uri} with method {$method} does not exist.",
    ], 404);
})->name('fallback');
