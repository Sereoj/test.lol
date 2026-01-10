<?php

namespace App\Models\Users;

use Database\Factories\UserSettingFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'is_online',
        'is_preferences_feed',
        'preferences_feed',
        'is_private',
        'show_online_status',
        'enable_two_factor'
    ];

    public $timestamps = true;

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): UserSettingFactory
    {
        return UserSettingFactory::new();
    }
}
