<?php

namespace App\Models\Users;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserPremiumFeature extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'has_no_ads',
        'has_premium_badge',
        'upload_limit',
        'max_file_size',
    ];

    protected $casts = [
        'has_no_ads' => 'boolean',
        'has_premium_badge' => 'boolean',
        'upload_limit' => 'integer',
        'max_file_size' => 'integer',
    ];

    /**
     * Отношение к пользователю
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Активировать все Premium функции
     */
    public function activatePremium(): void
    {
        $this->update([
            'has_no_ads' => true,
            'has_premium_badge' => true,
            'upload_limit' => 100,
            'max_file_size' => 500,
        ]);
    }

    /**
     * Деактивировать Premium функции (вернуть к обычным)
     */
    public function deactivatePremium(): void
    {
        $this->update([
            'has_no_ads' => false,
            'has_premium_badge' => false,
            'upload_limit' => 20,
            'max_file_size' => 50,
        ]);
    }

    /**
     * Проверить, есть ли у пользователя Premium
     */
    public function isPremium(): bool
    {
        return $this->has_premium_badge === true;
    }
}
