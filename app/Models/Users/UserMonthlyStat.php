<?php

namespace App\Models\Users;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserMonthlyStat extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'month',
        'year',
        'uploads_count',
    ];

    protected $casts = [
        'month' => 'integer',
        'year' => 'integer',
        'uploads_count' => 'integer',
    ];

    /**
     * Отношение к пользователю
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Инкрементировать счетчик загрузок
     */
    public function incrementUploads(): void
    {
        $this->increment('uploads_count');
    }

    /**
     * Получить или создать статистику за текущий месяц
     */
    public static function getOrCreateForCurrentMonth(int $userId): self
    {
        return self::firstOrCreate([
            'user_id' => $userId,
            'month' => now()->month,
            'year' => now()->year,
        ], [
            'uploads_count' => 0,
        ]);
    }

    /**
     * Проверить, превышен ли лимит загрузок
     */
    public function isLimitExceeded(int $limit): bool
    {
        return $this->uploads_count >= $limit;
    }
}
