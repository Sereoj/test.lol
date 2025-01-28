<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserSetting extends Model
{
    use HasFactory;

    protected $fillable = ['is_online', 'is_preferences_feed', 'preferences_feed'];

    public $timestamps = true;
}
