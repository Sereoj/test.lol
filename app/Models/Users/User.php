<?php

namespace App\Models\Users;

use App\Models\Apps\App;
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
use App\Models\Roles\Role;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    public $timestamps = true;

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
        'level_id',
        'role_id',
        'userSettings_id',
        'usingApps_id',
        'status_id',
        'location_id',
        'employment_status_id',
    ];

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
        return $this->belongsToMany(Badge::class, 'user_badge');
    }

    public function usingApps()
    {
        return $this->belongsTo(App::class, 'usingApps_id');
    }

    public function userSettings()
    {
        return $this->belongsTo(UserSetting::class, 'userSettings_id');
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

    public function currentAvatar()
    {
        return $this->hasOne(Avatar::class)->latest();
    }

    public function onlineStatus()
    {
        return $this->hasOne(UserOnlineStatus::class);
    }

    protected static function boot()
    {
        parent::boot();

        static::created(function ($user) {
            $level = UserLevel::query()->where('experience_required', 0)->first();
            $user->level()->associate($level);
            $user->save();
        });
    }
}
