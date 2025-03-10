<?php

namespace App\Models\Users;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class UserOnlineStatus extends Model
{
    use HasFactory;

    protected $table = 'online_status';

    protected $fillable = [
        'user_id',
        'last_activity',
        'device_type',
        'ip_address',
    ];

    protected $casts = [
        'last_activity' => 'datetime',
        'device_type' => 'string',
        'ip_address' => 'string',
    ];

    // Связь с пользователем
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function isOnline(int $minutes = 5): bool
    {
        return $this->last_activity->gt(now()->subMinutes($minutes));
    }
}
