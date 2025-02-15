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

    // Связь с пользователем
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function isOnline()
    {
        return Cache::remember("user_{$this->id}_online", 60, function () {
            return $this->onlineStatus && $this->onlineStatus->last_activity > now()->subMinutes(5);
        });
    }
}
