<?php

namespace App\Models;

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
        'username', 'slug', 'description', 'email', 'email_verified_at',
        'verification', 'password', 'provider', 'provider_id', 'role_id', 'usingApps_id', 'userSettings_id',
        'level_id', 'experience', 'gender', 'language', 'age', 'status_id', 'employment_status_id', 'location_id',
        'employment_status_id',
    ];

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
    ];

    public function level()
    {
        return $this->belongsTo(Level::class);
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
        return $this->belongsTo(Role::class);
    }

    public function userSettings()
    {
        return $this->belongsTo(UserSetting::class);
    }

    public function specializations()
    {
        return $this->belongsToMany(Specialization::class, 'user_specialization');
    }

    public function status()
    {
        return $this->belongsTo(Status::class);
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
        return $this->belongsTo(EmploymentStatus::class);
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function tasks()
    {
        return $this->belongsToMany(Task::class, 'user_tasks')->withPivot('progress', 'completed');
    }

    public function userBalance()
    {
        return $this->hasOne(UserBalance::class);
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

    protected static function boot()
    {
        parent::boot();

        static::created(function ($user) {
            $level = Level::query()->where('experience_required', 0)->first();
            $user->level()->associate($level);
            $user->save();
        });
    }
}
