<?php

namespace App\Models;

use App\Models\Users\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'plan',
        'status',
        'amount',
        'currency',
        'started_at',
        'expires_at',
    ];

    // Отношение с пользователем
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Проверка, активна ли подписка
    public function isActive(): bool
    {
        return $this->status === 'active' && $this->expires_at > now();
    }

    // Проверка на просроченную подписку
    public function isExpired(): bool
    {
        return $this->expires_at <= now();
    }
}
