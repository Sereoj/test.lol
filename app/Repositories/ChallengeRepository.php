<?php

namespace App\Repositories;

use App\Models\Challenge;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

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
} 