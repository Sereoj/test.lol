<?php

use App\Http\Controllers\Apps\AppController;
use App\Http\Controllers\Authentication\AuthController;
use App\Http\Controllers\Authentication\EmailVerificationController;
use App\Http\Controllers\Authentication\PasswordResetController;
use App\Http\Controllers\Authentication\SocialiteController;
use App\Http\Controllers\Authentication\StepController;
use App\Http\Controllers\Billing\BalanceController;
use App\Http\Controllers\Billing\PurchaseController;
use App\Http\Controllers\Billing\SubscriptionController;
use App\Http\Controllers\Billing\TransactionController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\EmploymentStatusController;
use App\Http\Controllers\InitController;
use App\Http\Controllers\Media\AvatarController;
use App\Http\Controllers\Posts\MediaController;
use App\Http\Controllers\Posts\PostController;
use App\Http\Controllers\Posts\PostSearchController;
use App\Http\Controllers\Posts\PostStatisticController;
use App\Http\Controllers\SourceController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\BadgeController;
use App\Http\Controllers\UserLevelController;
use App\Http\Controllers\UserLocationController;
use App\Http\Controllers\UserRoleController;
use App\Http\Controllers\Users\UserAchievementController;
use App\Http\Controllers\Users\UserController;
use App\Http\Controllers\Users\UserEmploymentStatusController;
use App\Http\Controllers\Users\UserFollowController;
use App\Http\Controllers\Users\UserLanguageController;
use App\Http\Controllers\Users\UserProfileController;
use App\Http\Controllers\Users\UserSourceController;
use App\Http\Controllers\Users\UserTaskController;
use App\Http\Controllers\UserSkillController;
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

// Socialite routes
Route::prefix('auth')->middleware('guest')->group(function () {
    Route::get('redirect/{provider}', [SocialiteController::class, 'redirectToProvider'])->name('auth.redirect'); // для гостей
    Route::get('callback/{provider}', [SocialiteController::class, 'handleProviderCallback'])->name('auth.callback'); // для гостей
});
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
        Route::get('/', [AuthController::class, 'user'])->name('user.user'); // для авторизированных

        // Баланс
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

        Route::prefix('subscriptions')->group(function (){
            Route::post('/', [SubscriptionController::class, 'createSubscription']);
            Route::get('/active', [SubscriptionController::class, 'getActiveSubscription']);
            Route::post('/{subscriptionId}/extend', [SubscriptionController::class, 'extendSubscription']);
        });

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
            Route::post('/', [UserSkillController::class, 'addSkill'])->middleware('role:admin')->name('user.skills.add'); // для администраторов
            Route::delete('/', [UserSkillController::class, 'removeSkill'])->middleware('role:admin')->name('user.skills.remove'); // для администраторов
            Route::get('/', [UserSkillController::class, 'getUserSkills'])->name('user.skills.get'); // для авторизированных
        });

        Route::prefix('badges')->group(function () {
            Route::post('/', [BadgeController::class, 'store'])->name('user-badges.store'); // для авторизированных
            Route::get('/active', [BadgeController::class, 'getActiveBadge'])->name('user-badges.get-active');
            Route::post('/active', [BadgeController::class, 'setActiveBadge'])->name('user-badges.set-active'); // для авторизированных
            Route::get('/', [BadgeController::class, 'index'])->name('user-badges.index'); // для авторизированных
            Route::get('/{id}', [BadgeController::class, 'show'])->name('user-badges.show'); // для авторизированных
            Route::put('/{id}', [BadgeController::class, 'update'])->name('user-badges.update'); // для авторизированных
            Route::delete('/{id}', [BadgeController::class, 'destroy'])->name('user-badges.destroy'); // для авторизированных
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

        Route::get('/{id}', [UserController::class, 'getUserProfile'])->name('user.index'); // для авторизированных
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
            Route::get('/{post}/summary', [PostStatisticController::class, 'getPostStatistics'])->name('posts.statistics.post'); // для авторизированных
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
        Route::post('/', [TagController::class, 'store'])->name('tags.store'); // для авторизированных
        Route::put('/{tag}', [TagController::class, 'update'])->name('tags.update'); // для авторизированных
        Route::middleware('role:admin')->group(function () {
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
        Route::get('/', [UserSkillController::class, 'index'])->name('skills.index'); // авторизированных

        Route::middleware(['role:admin'])->group(function () {
            Route::post('/', [UserSkillController::class, 'store'])->name('skills.store'); // для администраторов
            Route::get('/{id}', [UserSkillController::class, 'show'])->name('skills.show'); // для администраторов
            Route::put('/{id}', [UserSkillController::class, 'update'])->name('skills.update'); // для администраторов
            Route::delete('/{id}', [UserSkillController::class, 'destroy'])->name('skills.destroy'); // для администраторов
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
        Route::get('/', [UserLevelController::class, 'index'])->name('levels.index'); // для администраторов
        Route::post('/', [UserLevelController::class, 'store'])->name('levels.store'); // для администраторов
    });

    Route::prefix('roles')->middleware('role:admin')->group(function () {
        Route::get('/{role}', [UserRoleController::class, 'show'])->name('roles.show');  // авторизированных
        Route::resource('/', UserRoleController::class)->except(['show']);
    });

    Route::prefix('achievements')->group(function () {
        Route::get('/', [UserAchievementController::class, 'index'])->name('achievements.index'); // для авторизированных
        Route::post('/', [UserAchievementController::class, 'store'])->middleware('role:admin')->name('achievements.store'); // для администраторов
        Route::delete('/{achievement}', [UserAchievementController::class, 'destroy'])->middleware('role:admin')->name('achievements.destroy'); // для администраторов
    });

    Route::prefix('locations')->group(function () {
        Route::get('/', [UserLocationController::class, 'index'])->name('locations.index');
        Route::get('/{id}', [UserLocationController::class, 'show'])->name('locations.show');

        Route::middleware(['role:admin'])->group(function () {
            Route::post('/', [UserLocationController::class, 'store'])->name('locations.store');
            Route::put('/{id}', [UserLocationController::class, 'update'])->name('locations.update');
            Route::delete('/{id}', [UserLocationController::class, 'destroy'])->name('locations.destroy');
        });
    });

    Route::prefix('badges')->group(function () {
        Route::get('/', [BadgeController::class, 'index'])->name('badges.index');

        Route::middleware(['role:admin'])->group(function () {
            Route::get('/{id}', [BadgeController::class, 'show'])->name('badges.show');
            Route::post('/', [BadgeController::class, 'store'])->name('badges.store');
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

// Public posts route
Route::middleware('guest')->group(function () {
    Route::get('/init', [InitController::class, 'init'])->name('init.public');

    // Public routes
    Route::post('/language', [UserLanguageController::class, 'setLanguage'])->name('set.language'); // для гостей или авторизированных
    Route::post('/register', [AuthController::class, 'register'])->name('register.public'); // только для гостей
    Route::post('/login', [AuthController::class, 'login'])->name('login.public'); // только для гостей
    Route::post('/refresh-token', [AuthController::class, 'refreshToken'])->name('refresh-token'); // для авторизированных
    Route::post('/send-verification-code', [EmailVerificationController::class, 'sendVerificationCode'])->name('send.verification.code'); // для гостей
    Route::post('/verify-email', [EmailVerificationController::class, 'verifyEmail'])->name('verify.email'); // для гостей
    Route::post('/password/reset/email', [PasswordResetController::class, 'sendPasswordResetEmail'])->name('password.reset.email'); // только для гостей
    Route::post('/password/reset', [PasswordResetController::class, 'resetPassword'])->name('password.reset'); // только для гостей

    Route::prefix('search')->group(function () {
        Route::get('/', [PostSearchController::class, 'search'])->name('posts.search.public'); // для гостей
        Route::get('/suggest', [PostSearchController::class, 'suggest'])->name('posts.suggest.public'); // для гостей
    });

    Route::prefix('user')->group(function () {
    });

    Route::prefix('posts')->group(function () {
        Route::get('/', [PostController::class, 'index'])->name('posts.index.public');
        Route::get('/{id}', [PostController::class, 'show'])->name('posts.show.public');

        Route::prefix('{post_id}/comments')->group(function () {
            Route::get('/', [CommentController::class, 'index'])->name('comments.index.public');
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
