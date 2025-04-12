<?php

namespace App\Services;

use App\Models\Challenge;
use App\Repositories\ChallengeRepository;
use Exception;
use Illuminate\Pagination\LengthAwarePaginator;

class ChallengeService extends BaseService
{
    protected ChallengeRepository $challengeRepository;

    /**
     * ChallengeService constructor.
     *
     * @param ChallengeRepository $challengeRepository
     */
    public function __construct(ChallengeRepository $challengeRepository)
    {
        $this->challengeRepository = $challengeRepository;
    }

    /**
     * Получить все челленджи.
     *
     * @param int $perPage
     * @param array $filters
     * @return LengthAwarePaginator
     * @throws Exception
     */
    public function getAll(int $perPage = 10, array $filters = []): LengthAwarePaginator
    {
        try {
            return $this->challengeRepository->getAllChallenges($perPage, $filters);
        } catch (Exception $e) {
            throw new Exception('Ошибка при получении челленджей: ' . $e->getMessage());
        }
    }

    /**
     * Получить челлендж по ID.
     *
     * @param int $id
     * @return Challenge
     * @throws Exception
     */
    public function getById(int $id): Challenge
    {
        try {
            return $this->challengeRepository->getChallengeById($id);
        } catch (Exception $e) {
            throw new Exception('Челлендж не найден');
        }
    }

    /**
     * Создать новый челлендж.
     *
     * @param array $data
     * @return Challenge
     * @throws Exception
     */
    public function create(array $data): Challenge
    {
        try {
            return $this->challengeRepository->createChallenge($data);
        } catch (Exception $e) {
            throw new Exception('Ошибка при создании челленджа: ' . $e->getMessage());
        }
    }

    /**
     * Обновить челлендж.
     *
     * @param int $id
     * @param array $data
     * @return Challenge
     * @throws Exception
     */
    public function update(int $id, array $data): Challenge
    {
        try {
            return $this->challengeRepository->updateChallenge($id, $data);
        } catch (Exception $e) {
            throw new Exception('Ошибка при обновлении челленджа: ' . $e->getMessage());
        }
    }

    /**
     * Удалить челлендж.
     *
     * @param int $id
     * @return bool
     * @throws Exception
     */
    public function delete($id): bool
    {
        try {
            return $this->challengeRepository->deleteChallenge($id);
        } catch (Exception $e) {
            throw new Exception('Ошибка при удалении челленджа: ' . $e->getMessage());
        }
    }

    /**
     * Получить активные челленджи.
     *
     * @param int $perPage
     * @return LengthAwarePaginator
     * @throws Exception
     */
    public function getActiveChallenges(int $perPage = 10): LengthAwarePaginator
    {
        try {
            return $this->challengeRepository->getActiveChallenges($perPage);
        } catch (Exception $e) {
            throw new Exception('Ошибка при получении активных челленджей: ' . $e->getMessage());
        }
    }

    /**
     * Получить предстоящие челленджи.
     *
     * @param int $perPage
     * @return LengthAwarePaginator
     * @throws Exception
     */
    public function getUpcomingChallenges(int $perPage = 10): LengthAwarePaginator
    {
        try {
            return $this->challengeRepository->getUpcomingChallenges($perPage);
        } catch (Exception $e) {
            throw new Exception('Ошибка при получении предстоящих челленджей: ' . $e->getMessage());
        }
    }

    /**
     * Получить завершенные челленджи.
     *
     * @param int $perPage
     * @return LengthAwarePaginator
     * @throws Exception
     */
    public function getCompletedChallenges(int $perPage = 10): LengthAwarePaginator
    {
        try {
            return $this->challengeRepository->getCompletedChallenges($perPage);
        } catch (Exception $e) {
            throw new Exception('Ошибка при получении завершенных челленджей: ' . $e->getMessage());
        }
    }

    /**
     * Добавить пользователя к челленджу.
     *
     * @param int $challengeId
     * @param int $userId
     * @throws Exception
     */
    public function addParticipant(int $challengeId, int $userId): void
    {
        try {
            $this->challengeRepository->addParticipant($challengeId, $userId);
        } catch (Exception $e) {
            throw new Exception('Ошибка при добавлении участника: ' . $e->getMessage());
        }
    }

    /**
     * Удалить пользователя из челленджа.
     *
     * @param int $challengeId
     * @param int $userId
     * @throws Exception
     */
    public function removeParticipant(int $challengeId, int $userId): void
    {
        try {
            $this->challengeRepository->removeParticipant($challengeId, $userId);
        } catch (Exception $e) {
            throw new Exception('Ошибка при удалении участника: ' . $e->getMessage());
        }
    }

    /**
     * Получить челленджи пользователя.
     *
     * @param int $userId
     * @param int $perPage
     * @return LengthAwarePaginator
     * @throws Exception
     */
    public function getUserChallenges(int $userId, int $perPage = 10): LengthAwarePaginator
    {
        try {
            return $this->challengeRepository->getUserChallenges($userId, $perPage);
        } catch (Exception $e) {
            throw new Exception('Ошибка при получении челленджей пользователя: ' . $e->getMessage());
        }
    }
}