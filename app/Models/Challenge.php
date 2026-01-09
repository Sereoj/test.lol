<?php

namespace App\Models;

use App\Models\Content\Media;
use App\Models\Posts\Post;
use App\Models\Users\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
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
        'organizer_id',
        'type',
        'winner_selection_method',
        'title',
        'description',
        'cover_path',
        'prize_amount',
        'prize_currency',
        'platform_fee_percentage',
        'platform_fee_amount',
        'net_prize_amount',
        'participants_count',
        'submissions_count',
        'votes_count',
        'start_date',
        'end_date',
        'voting_end_date',
        'results_announced_at',
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
        'voting_end_date',
        'results_announced_at',
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
        'platform_fee_percentage' => 'decimal:2',
        'platform_fee_amount' => 'decimal:2',
        'net_prize_amount' => 'decimal:2',
        'participants_count' => 'integer',
        'submissions_count' => 'integer',
        'votes_count' => 'integer',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'voting_end_date' => 'datetime',
        'results_announced_at' => 'datetime',
    ];

    /**
     * Организатор челленджа.
     *
     * @return BelongsTo
     */
    public function organizer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'organizer_id');
    }

    /**
     * Пользователи, участвующие в челлендже.
     *
     * @return BelongsToMany
     */
    public function participants(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'challenge_user')
            ->withPivot(['has_submitted', 'submitted_at'])
            ->withTimestamps();
    }

    /**
     * Призовые места.
     *
     * @return HasMany
     */
    public function prizes(): HasMany
    {
        return $this->hasMany(ChallengePrize::class)->orderBy('place');
    }

    /**
     * Работы (посты участников).
     *
     * @return HasMany
     */
    public function submissions(): HasMany
    {
        return $this->hasMany(Post::class, 'challenge_id');
    }

    /**
     * Голоса.
     *
     * @return HasMany
     */
    public function votes(): HasMany
    {
        return $this->hasMany(ChallengeVote::class);
    }

    /**
     * Победители.
     *
     * @return HasMany
     */
    public function winners(): HasMany
    {
        return $this->hasMany(ChallengeWinner::class)->orderBy('place');
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

    /**
     * Челленджи от пользователей.
     *
     * @param $query
     * @return mixed
     */
    public function scopeUserChallenges($query)
    {
        return $query->where('type', 'user');
    }

    /**
     * Официальные челленджи.
     *
     * @param $query
     * @return mixed
     */
    public function scopeOfficialChallenges($query)
    {
        return $query->where('type', 'official');
    }

    /**
     * С ручным выбором победителей.
     *
     * @param $query
     * @return mixed
     */
    public function scopeManualSelection($query)
    {
        return $query->where('winner_selection_method', 'manual');
    }

    /**
     * С публичным голосованием.
     *
     * @param $query
     * @return mixed
     */
    public function scopePublicVoting($query)
    {
        return $query->where('winner_selection_method', 'voting_public');
    }

    /**
     * С голосованием участников.
     *
     * @param $query
     * @return mixed
     */
    public function scopeParticipantsVoting($query)
    {
        return $query->where('winner_selection_method', 'voting_participants');
    }

    /**
     * В процессе голосования.
     *
     * @param $query
     * @return mixed
     */
    public function scopeVoting($query)
    {
        return $query->where('status', 'voting');
    }

    /**
     * Ожидающие выбора победителей.
     *
     * @param $query
     * @return mixed
     */
    public function scopeSelectingWinners($query)
    {
        return $query->where('status', 'selecting_winners');
    }

    /**
     * Проверка, является ли пользователь организатором.
     *
     * @param int $userId
     * @return bool
     */
    public function isOrganizer(int $userId): bool
    {
        return $this->organizer_id === $userId;
    }

    /**
     * Проверка, может ли пользователь голосовать.
     *
     * @param int $userId
     * @return bool
     */
    public function canUserVote(int $userId): bool
    {
        if ($this->winner_selection_method === 'manual') {
            return false;
        }

        if ($this->winner_selection_method === 'voting_participants') {
            return $this->participants()->where('user_id', $userId)->exists();
        }

        // voting_public - любой может голосовать
        return true;
    }

    /**
     * Проверка, проголосовал ли пользователь.
     *
     * @param int $userId
     * @return bool
     */
    public function hasUserVoted(int $userId): bool
    {
        return $this->votes()->where('user_id', $userId)->exists();
    }

    /**
     * Проверка, может ли пользователь подать работу.
     *
     * @return bool
     */
    public function canSubmit(): bool
    {
        return $this->status === 'active' &&
               now()->between($this->start_date, $this->end_date);
    }

    /**
     * Проверка, завершен ли прием работ.
     *
     * @return bool
     */
    public function isSubmissionClosed(): bool
    {
        return $this->status !== 'active' || now()->greaterThan($this->end_date);
    }

    /**
     * Проверка, идет ли голосование.
     *
     * @return bool
     */
    public function isVotingOpen(): bool
    {
        if ($this->status !== 'voting') {
            return false;
        }

        if ($this->voting_end_date) {
            return now()->lessThanOrEqualTo($this->voting_end_date);
        }

        return true;
    }
} 