<?php

namespace App\Services\Users;

use App\Models\Employment\EmploymentStatus;
use App\Models\Users\User;
use App\Services\Base\SimpleService;
use Exception;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;

/**
 * Сервис для работы со статусами занятости пользователей
 */
class UserEmploymentStatusService extends SimpleService
{
    /**
     * Префикс кеша
     *
     * @var string
     */
    protected string $cachePrefix = 'user_employment_status';

    /**
     * Время хранения кеша в минутах
     *
     * @var int
     */
    protected int $defaultCacheMinutes = 60;

    /**
     * Конструктор
     */
    public function __construct()
    {
        parent::__construct();
        $this->setLogPrefix('UserEmploymentStatusService');
    }

    /**
     * Назначить статус занятости пользователю
     *
     * @param int $userId ID пользователя
     * @param int $employmentStatusId ID статуса занятости
     * @return User|null
     * @throws ResourceNotFoundException
     */
    public function assignEmploymentStatusToUser($userId, $employmentStatusId)
    {
        $this->logInfo('Назначение статуса занятости пользователю', [
            'user_id' => $userId,
            'employment_status_id' => $employmentStatusId
        ]);

        return $this->transaction(function () use ($userId, $employmentStatusId) {
            try {
                $user = User::find($userId);
                if (!$user) {
                    $this->logWarning('Пользователь не найден', ['user_id' => $userId]);
                    throw new ResourceNotFoundException('Пользователь не найден');
                }

                $employmentStatus = EmploymentStatus::find($employmentStatusId);
                if (!$employmentStatus) {
                    $this->logWarning('Статус занятости не найден', ['employment_status_id' => $employmentStatusId]);
                    throw new ResourceNotFoundException('Статус занятости не найден');
                }

                $user->employmentStatus()->associate($employmentStatus);
                $user->save();

                // Очистка кеша пользователя
                $this->forgetCache($this->buildCacheKey('user', [$userId]));

                $this->logInfo('Статус занятости успешно назначен пользователю', [
                    'user_id' => $userId,
                    'employment_status_id' => $employmentStatusId
                ]);

                return $user;
            } catch (ResourceNotFoundException $e) {
                $this->logError('Ресурс не найден при назначении статуса занятости', [
                    'user_id' => $userId,
                    'employment_status_id' => $employmentStatusId
                ], $e);
                throw $e;
            } catch (Exception $e) {
                $this->logError('Ошибка при назначении статуса занятости пользователю', [
                    'user_id' => $userId,
                    'employment_status_id' => $employmentStatusId
                ], $e);
                return null;
            }
        });
    }

    /**
     * Удалить статус занятости у пользователя
     *
     * @param int $userId ID пользователя
     * @return User|null
     * @throws ResourceNotFoundException
     */
    public function removeEmploymentStatusFromUser($userId)
    {
        $this->logInfo('Удаление статуса занятости у пользователя', ['user_id' => $userId]);

        return $this->transaction(function () use ($userId) {
            try {
                $user = User::find($userId);
                if (!$user) {
                    $this->logWarning('Пользователь не найден', ['user_id' => $userId]);
                    throw new ResourceNotFoundException('Пользователь не найден');
                }

                $currentStatusId = $user->employment_status_id;

                $user->employmentStatus()->dissociate();
                $user->save();

                // Очистка кеша пользователя
                $this->forgetCache($this->buildCacheKey('user', [$userId]));

                $this->logInfo('Статус занятости успешно удален у пользователя', [
                    'user_id' => $userId,
                    'previous_status_id' => $currentStatusId
                ]);

                return $user;
            } catch (ResourceNotFoundException $e) {
                $this->logError('Ресурс не найден при удалении статуса занятости', [
                    'user_id' => $userId
                ], $e);
                throw $e;
            } catch (Exception $e) {
                $this->logError('Ошибка при удалении статуса занятости у пользователя', [
                    'user_id' => $userId
                ], $e);
                return null;
            }
        });
    }

    /**
     * Получить статус занятости пользователя
     *
     * @param int $userId ID пользователя
     * @return EmploymentStatus|null
     */
    public function getUserEmploymentStatus($userId)
    {
        $cacheKey = $this->buildCacheKey('user', [$userId]);

        return $this->getFromCacheOrStore($cacheKey, $this->defaultCacheMinutes, function () use ($userId) {
            $this->logInfo('Получение статуса занятости пользователя', ['user_id' => $userId]);

            try {
                $user = User::find($userId);
                if (!$user) {
                    $this->logWarning('Пользователь не найден', ['user_id' => $userId]);
                    return null;
                }

                $employmentStatus = $user->employmentStatus;

                if (!$employmentStatus) {
                    $this->logInfo('У пользователя нет статуса занятости', ['user_id' => $userId]);
                    return null;
                }

                $this->logInfo('Получен статус занятости пользователя', [
                    'user_id' => $userId,
                    'employment_status_id' => $employmentStatus->id
                ]);

                return $employmentStatus;
            } catch (Exception $e) {
                $this->logError('Ошибка при получении статуса занятости пользователя', [
                    'user_id' => $userId
                ], $e);
                return null;
            }
        });
    }
}
