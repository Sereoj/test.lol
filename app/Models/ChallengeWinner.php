<?php

namespace App\Models;

use App\Models\Billing\Transaction;
use App\Models\Posts\Post;
use App\Models\Users\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChallengeWinner extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'challenge_id',
        'user_id',
        'post_id',
        'place',
        'prize_amount',
        'prize_currency',
        'payout_status',
        'transaction_id',
        'payout_completed_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'place' => 'integer',
        'prize_amount' => 'decimal:2',
        'payout_completed_at' => 'datetime',
    ];

    /**
     * Челлендж.
     *
     * @return BelongsTo
     */
    public function challenge(): BelongsTo
    {
        return $this->belongsTo(Challenge::class);
    }

    /**
     * Победитель.
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Победившая работа.
     *
     * @return BelongsTo
     */
    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    /**
     * Транзакция выплаты приза.
     *
     * @return BelongsTo
     */
    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    /**
     * Проверка, выплачен ли приз.
     *
     * @return bool
     */
    public function isPaid(): bool
    {
        return $this->payout_status === 'completed';
    }
}
