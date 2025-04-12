<?php

namespace App\Models;

use App\Models\Content\Media;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Challenge extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Атрибуты, которые можно массово назначать.
     *
     * @var array
     */
    protected $fillable = [
        'title',
        'description',
        'cover_path',
        'prize_amount',
        'prize_currency',
        'participants_count',
        'start_date',
        'end_date',
        'status',
    ];

    /**
     * Атрибуты, которые должны быть преобразованы в даты.
     *
     * @var array
     */
    protected $dates = [
        'start_date',
        'end_date',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    /**
     * Скрытые атрибуты.
     *
     * @var array
     */
    protected $hidden = [
        'deleted_at',
    ];

    /**
     * Атрибуты, которые должны быть приведены к типам.
     *
     * @var array
     */
    protected $casts = [
        'prize_amount' => 'decimal:2',
        'participants_count' => 'integer',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
    ];

    /**
     * Пользователи, участвующие в челлендже.
     *
     * @return BelongsToMany
     */
    public function participants(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'challenge_user')
            ->withPivot('submission_data')
            ->withTimestamps();
    }

    /**
     * Получить информацию о призовом фонде.
     *
     * @return array
     */
    public function getPrizePoolAttribute(): array
    {
        return [
            'amount' => $this->prize_amount,
            'currency' => $this->prize_currency
        ];
    }

    /**
     * Получить обложку челленджа.
     *
     * @return array|null
     */
    public function getCoverAttribute(): ?array
    {
        if (!$this->cover_path) {
            return null;
        }

        return [
            'path' => $this->cover_path
        ];
    }

    /**
     * Проверить, участвует ли текущий пользователь в челлендже.
     *
     * @return bool
     */
    public function getIsParticipatingAttribute(): bool
    {
        if (!auth()->check()) {
            return false;
        }

        return $this->participants()->where('user_id', auth()->id())->exists();
    }

    /**
     * Активные челленджи.
     *
     * @param $query
     * @return mixed
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Предстоящие челленджи.
     *
     * @param $query
     * @return mixed
     */
    public function scopeUpcoming($query)
    {
        return $query->where('start_date', '>', now());
    }

    /**
     * Завершенные челленджи.
     *
     * @param $query
     * @return mixed
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }
} 