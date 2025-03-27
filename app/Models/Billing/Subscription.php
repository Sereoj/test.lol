<?php

namespace App\Models\Billing;

use App\Models\Users\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

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
    protected $casts = [
      'started_at' => 'datetime',
      'expires_at' => 'datetime',
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

    public function extendSubscription($duration)
    {
        // Проверяем, что duration является числом
        if (!is_numeric($duration) || $duration <= 0) {
            throw new InvalidArgumentException('Duration must be a positive number.');
        }

        // Продлеваем подписку
        $this->expires_at = $this->expires_at->addDays($duration);
        $this->save();
    }

    public function updateStatus(): void
    {
        if ($this->isExpired()) {
            $this->status = 'expired';
            $this->save();
        }
    }
}
