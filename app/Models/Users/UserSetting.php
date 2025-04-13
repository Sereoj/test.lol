<?php

namespace App\Models\Users;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Database\Factories\UserSettingFactory;

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
}
