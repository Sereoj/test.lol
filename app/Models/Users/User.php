<?php

namespace App\Models\Users;

use App\Models\Apps\App;
use App\Models\Billing\Subscription;
use App\Models\Billing\Transaction;
use App\Models\Content\Achievement;
use App\Models\Content\Badge;
use App\Models\Content\Skill;
use App\Models\Content\Source;
use App\Models\Content\Specialization;
use App\Models\Content\Task;
use App\Models\Employment\EmploymentStatus;
use App\Models\Locations\Location;
use App\Models\Media\Avatar;
use App\Models\Posts\Post;
use App\Models\Roles\Role;
use App\Services\Media\StorageService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use OpenApi\Attributes as OA;

/**
 * @OA\Schema(
 *     schema="User",
 *     type="object",
 *     title="User",
 *     description="User model",
 *     @OA\Property(property="id", type="integer", example=1, description="User ID"),
 *     @OA\Property(property="username", type="string", example="john_doe", description="Username"),
 *     @OA\Property(property="slug", type="string", example="john-doe", description="URL slug"),
 *     @OA\Property(property="description", type="string", example="Web developer", description="User description"),
 *     @OA\Property(property="cover", type="string", example="covers/user1.jpg", description="Cover image path"),
 *     @OA\Property(property="website", type="string", example="https://example.com", description="User website"),
 *     @OA\Property(property="email", type="string", format="email", example="john@example.com", description="Email address"),
 *     @OA\Property(property="email_verified_at", type="string", format="date-time", nullable=true, description="Email verification timestamp"),
 *     @OA\Property(property="verification", type="boolean", example=true, description="Verification status"),
 *     @OA\Property(property="experience", type="integer", example=100, description="User experience points"),
 *     @OA\Property(property="gender", type="string", example="male", description="User gender"),
 *     @OA\Property(property="language", type="string", example="en", description="Preferred language"),
 *     @OA\Property(property="age", type="integer", example=25, description="User age"),
 *     @OA\Property(property="provider", type="string", nullable=true, example="google", description="OAuth provider"),
 *     @OA\Property(property="provider_id", type="string", nullable=true, description="OAuth provider ID"),
 *     @OA\Property(property="role_id", type="integer", nullable=true, description="Role ID"),
 *     @OA\Property(property="status_id", type="integer", nullable=true, description="Status ID"),
 *     @OA\Property(property="employment_status_id", type="integer", nullable=true, description="Employment status ID"),
 *     @OA\Property(property="location_id", type="integer", nullable=true, description="Location ID"),
 *     @OA\Property(property="url", type="string", example="https://cdn.example.com/covers/user1.jpg", description="Full URL to cover image"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="Creation timestamp"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="Last update timestamp"),
 *     @OA\Property(property="deleted_at", type="string", format="date-time", nullable=true, description="Deletion timestamp")
 * )
 */
class User extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use Notifiable;
    use SoftDeletes;

    public $timestamps = true;

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'username',
        'seo_meta',
        'slug',
        'description',
        'cover',
        'disk',
        'website',
        'email',
        'email_verified_at',
        'verification',
        'experience',
        'gender',
        'language',
        'age',
        'password',
        'provider',
        'provider_id',
        'role_id',
        'userSettings_id',
        'usingApps_id',
        'status_id',
        'employment_status_id',
        'location_id'
    ];

    protected $appends = ['url'];

    public function isProfileComplete(): bool
    {
        return ! is_null($this->username) &&
            ! is_null($this->description) &&
            ! is_null($this->email_verified_at) &&
            ! is_null($this->usingApps_id) &&
            ! is_null($this->gender) &&
            ! is_null($this->status_id) &&
            ! is_null($this->employment_status_id) &&
            ! is_null($this->location_id);
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'experience' => 'integer',
        'verification' => 'boolean'
    ];

    public function level()
    {
        return $this->belongsTo(UserLevel::class);
    }

    public function achievements()
    {
        return $this->belongsToMany(Achievement::class, 'user_achievements');
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function badges()
    {
        return $this->belongsToMany(Badge::class, 'user_badge')
            ->withPivot('is_active')
            ->withTimestamps();
    }

    public function usingApps()
    {
        return $this->belongsToMany(App::class, 'user_app');
    }

    public function userSettings()
    {
        return $this->belongsTo(UserSetting::class, 'userSettings_id');
    }

    /**
     * Получить настройки уведомлений пользователя
     */
    public function notificationSettings()
    {
        return $this->hasOne(NotificationSetting::class)->withDefault();
    }


    public function specializations()
    {
        return $this->belongsToMany(Specialization::class, 'user_specialization');
    }

    public function status()
    {
        return $this->belongsTo(UserStatus::class);
    }

    // Подписки (Following)
    public function following()
    {
        return $this->belongsToMany(User::class, 'follows', 'follower_id', 'following_id')->withTimestamps();
    }

    // Подписчики (Followers)
    public function followers()
    {
        return $this->belongsToMany(User::class, 'follows', 'following_id', 'follower_id')->withTimestamps();
    }

    public function employmentStatus()
    {
        return $this->belongsTo(EmploymentStatus::class, 'employment_status_id');
    }

    public function location()
    {
        return $this->belongsTo(Location::class, 'location_id');
    }

    public function tasks()
    {
        return $this->belongsToMany(Task::class, 'user_tasks')->withPivot('progress', 'completed');
    }

    public function userBalance()
    {
        return $this->hasMany(UserBalance::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function sources()
    {
        return $this->belongsToMany(Source::class, 'source_user');
    }

    public function skills()
    {
        return $this->belongsToMany(Skill::class, 'skill_user');
    }

    public function avatars()
    {
        return $this->hasMany(Avatar::class);
    }

    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    public function currentAvatar()
    {
        return $this->hasOne(Avatar::class)->latest();
    }

    public function getUrlAttribute()
    {
        return StorageService::getPath($this->cover);
    }

    public function onlineStatus()
    {
        return $this->hasOne(UserOnlineStatus::class);
    }

    /**
     * Отношение к подпискам
     */
    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    /**
     * Отношение к активной подписке
     */
    public function activeSubscription()
    {
        return $this->hasOne(Subscription::class)
            ->where('status', 'active')
            ->where('expires_at', '>', now());
    }

    /**
     * Отношение к Premium функциям
     */
    public function premiumFeatures()
    {
        return $this->hasOne(UserPremiumFeature::class);
    }

    /**
     * Отношение к месячной статистике
     */
    public function monthlyStats()
    {
        return $this->hasMany(UserMonthlyStat::class);
    }

    /**
     * Получить статистику за текущий месяц
     */
    public function currentMonthStats()
    {
        return $this->hasOne(UserMonthlyStat::class)
            ->where('month', now()->month)
            ->where('year', now()->year);
    }

    /**
     * Проверить, есть ли у пользователя активная подписка
     */
    public function hasPremiumSubscription(): bool
    {
        return $this->activeSubscription()->exists();
    }

    /**
     * Проверяет, имеет ли пользователь указанную роль
     *
     * @param string $roleName Название роли для проверки
     * @return bool
     */
    public function hasRole(string $roleName): bool
    {
        return $this->role && $this->role->type === $roleName;
    }

    protected static function boot()
    {
        parent::boot();

        static::created(function ($user) {
            $level = UserLevel::query()->where('experience_required', 0)->first();
            $user->level()->associate($level);
            $user->save();

            // Создаем Premium функции с дефолтными значениями
            UserPremiumFeature::create([
                'user_id' => $user->id,
                'has_no_ads' => false,
                'has_premium_badge' => false,
                'upload_limit' => 20,
                'max_file_size' => 50,
            ]);
        });
    }
}
