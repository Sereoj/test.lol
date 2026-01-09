<?php

namespace App\Repositories;

use App\Models\Challenge;
use App\Models\ChallengeVote;
use App\Models\ChallengeWinner;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class ChallengeRepository
{
    /**
     * Получить все челленджи.
     *
     * @param int $perPage
     * @param array $filters
     * @return LengthAwarePaginator
     */
    public function getAllChallenges(int $perPage = 10, array $filters = []): LengthAwarePaginator
    {
        $query = Challenge::query();

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    /**
     * Получить челлендж по ID.
     *
     * @param int $id
     * @return Challenge
     */
    public function getChallengeById(int $id): Challenge
    {
        return Challenge::findOrFail($id);
    }

    /**
     * Создать новый челлендж.
     *
     * @param array $data
     * @return Challenge
     */
    public function createChallenge(array $data): Challenge
    {
        return Challenge::create($data);
    }

    /**
     * Обновить челлендж.
     *
     * @param int $id
     * @param array $data
     * @return Challenge
     */
    public function updateChallenge(int $id, array $data): Challenge
    {
        $challenge = $this->getChallengeById($id);
        $challenge->update($data);
        return $challenge;
    }

    /**
     * Удалить челлендж.
     *
     * @param int $id
     * @return bool
     */
    public function deleteChallenge(int $id): bool
    {
        $challenge = $this->getChallengeById($id);
        return $challenge->delete();
    }

    /**
     * Получить активные челленджи.
     *
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getActiveChallenges(int $perPage = 10): LengthAwarePaginator
    {
        return Challenge::active()->orderBy('start_date', 'asc')->paginate($perPage);
    }

    /**
     * Получить предстоящие челленджи.
     *
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getUpcomingChallenges(int $perPage = 10): LengthAwarePaginator
    {
        return Challenge::upcoming()->orderBy('start_date', 'asc')->paginate($perPage);
    }

    /**
     * Получить завершенные челленджи.
     *
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getCompletedChallenges(int $perPage = 10): LengthAwarePaginator
    {
        return Challenge::completed()->orderBy('end_date', 'desc')->paginate($perPage);
    }

    /**
     * Добавить пользователя к челленджу.
     *
     * @param int $challengeId
     * @param int $userId
     * @return void
     */
    public function addParticipant(int $challengeId, int $userId): void
    {
        $challenge = $this->getChallengeById($challengeId);
        if (!$challenge->participants()->where('user_id', $userId)->exists()) {
            $challenge->participants()->attach($userId);
            $challenge->increment('participants_count');
        }
    }

    /**
     * Удалить пользователя из челленджа.
     *
     * @param int $challengeId
     * @param int $userId
     * @return void
     */
    public function removeParticipant(int $challengeId, int $userId): void
    {
        $challenge = $this->getChallengeById($challengeId);
        if ($challenge->participants()->where('user_id', $userId)->exists()) {
            $challenge->participants()->detach($userId);
            $challenge->decrement('participants_count');
        }
    }

    /**
     * Получить челленджи пользователя.
     *
     * @param int $userId
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getUserChallenges(int $userId, int $perPage = 10): LengthAwarePaginator
    {
        return Challenge::whereHas('participants', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })->paginate($perPage);
    }

    /**
     * Создать челлендж с призами.
     *
     * @param array $data
     * @param array $prizes
     * @return Challenge
     */
    public function createChallengeWithPrizes(array $data, array $prizes): Challenge
    {
        $challenge = Challenge::create($data);

        foreach ($prizes as $prize) {
            $challenge->prizes()->create($prize);
        }

        return $challenge->load('prizes');
    }

    /**
     * Получить челленджи по типу.
     *
     * @param string $type
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getChallengesByType(string $type, int $perPage = 10): LengthAwarePaginator
    {
        return Challenge::where('type', $type)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Получить челленджи организатора.
     *
     * @param int $organizerId
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getOrganizerChallenges(int $organizerId, int $perPage = 10): LengthAwarePaginator
    {
        return Challenge::where('organizer_id', $organizerId)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Получить работы челленджа.
     *
     * @param int $challengeId
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getChallengeSubmissions(int $challengeId, int $perPage = 10): LengthAwarePaginator
    {
        $challenge = $this->getChallengeById($challengeId);
        return $challenge->submissions()
            ->with(['user', 'media'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Обновить счетчики челленджа.
     *
     * @param int $challengeId
     * @return void
     */
    public function updateChallengeCounters(int $challengeId): void
    {
        $challenge = $this->getChallengeById($challengeId);

        $challenge->update([
            'submissions_count' => $challenge->submissions()->count(),
            'votes_count' => $challenge->votes()->count(),
        ]);
    }

    /**
     * Обновить статус челленджа.
     *
     * @param int $challengeId
     * @param string $status
     * @return Challenge
     */
    public function updateStatus(int $challengeId, string $status): Challenge
    {
        $challenge = $this->getChallengeById($challengeId);
        $challenge->update(['status' => $status]);
        return $challenge;
    }

    /**
     * Добавить голос.
     *
     * @param int $challengeId
     * @param int $userId
     * @param int $postId
     * @return ChallengeVote
     */
    public function addVote(int $challengeId, int $userId, int $postId): ChallengeVote
    {
        return ChallengeVote::create([
            'challenge_id' => $challengeId,
            'user_id' => $userId,
            'post_id' => $postId,
        ]);
    }

    /**
     * Удалить голос.
     *
     * @param int $challengeId
     * @param int $userId
     * @return bool
     */
    public function removeVote(int $challengeId, int $userId): bool
    {
        return ChallengeVote::where('challenge_id', $challengeId)
            ->where('user_id', $userId)
            ->delete();
    }

    /**
     * Получить топ работ по голосам.
     *
     * @param int $challengeId
     * @param int $limit
     * @return Collection
     */
    public function getTopSubmissionsByVotes(int $challengeId, int $limit): Collection
    {
        return ChallengeVote::where('challenge_id', $challengeId)
            ->select('post_id', DB::raw('COUNT(*) as votes_count'))
            ->groupBy('post_id')
            ->orderBy('votes_count', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Добавить победителя.
     *
     * @param array $winnerData
     * @return ChallengeWinner
     */
    public function addWinner(array $winnerData): ChallengeWinner
    {
        return ChallengeWinner::create($winnerData);
    }

    /**
     * Получить победителей челленджа.
     *
     * @param int $challengeId
     * @return Collection
     */
    public function getWinners(int $challengeId): Collection
    {
        return ChallengeWinner::where('challenge_id', $challengeId)
            ->with(['user', 'post'])
            ->orderBy('place')
            ->get();
    }

    /**
     * Отметить, что пользователь подал работу.
     *
     * @param int $challengeId
     * @param int $userId
     * @return void
     */
    public function markAsSubmitted(int $challengeId, int $userId): void
    {
        $challenge = $this->getChallengeById($challengeId);
        $challenge->participants()->updateExistingPivot($userId, [
            'has_submitted' => true,
            'submitted_at' => now(),
        ]);
    }
} 