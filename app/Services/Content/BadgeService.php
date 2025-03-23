<?php

namespace App\Services\Content;

use App\Models\Content\Badge;
use App\Services\Base\SimpleService;
use Exception;

class BadgeService
{
    /**
     * Префикс кеша
     *
     * @var string
     */
    protected string $cachePrefix = 'badge';

    /**
     * Время хранения кеша в минутах
     *
     * @var int
     */
    protected int $defaultCacheMinutes = 120;

    /**
     * Конструктор
     */
    public function __construct()
    {
        parent::__construct();
        $this->setLogPrefix('BadgeService');
    }

    /**
     * Получить все значки
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllBadges()
    {
        $cacheKey = $this->buildCacheKey('all_badges');

        return $this->getFromCacheOrStore($cacheKey, $this->defaultCacheMinutes, function () {
            $this->logInfo("Получение всех значков");
            return Badge::all();
        });
    }

    /**
     * Получить значок по ID
     *
     * @param int $id ID значка
     * @return Badge
     * @throws Exception
     */
    public function getBadgeById($id): Badge
    {
        $cacheKey = $this->buildCacheKey('badge', [$id]);

        return $this->getFromCacheOrStore($cacheKey, $this->defaultCacheMinutes, function () use ($id) {
            $this->logInfo("Получение значка по ID", ['badge_id' => $id]);

            try {
                return Badge::query()->findOrFail($id);
            } catch (Exception $e) {
                $this->logWarning("Значок не найден", ['badge_id' => $id]);
                throw new Exception("Значок с ID {$id} не найден");
            }
        });
    }

    /**
     * Создать новый значок
     *
     * @param array $data Данные значка
     * @return Badge
     * @throws Exception
     */
    public function createBadge(array $data)
    {
        $this->logInfo("Создание нового значка", ['name' => $data['name'] ?? 'не указано']);

        return $this->transaction(function () use ($data) {
            try {
                $badge = Badge::query()->create($data);

                // Сбрасываем кеш
                $this->forgetCache($this->buildCacheKey('all_badges'));

                $this->logInfo("Значок успешно создан", ['badge_id' => $badge->id]);

                return $badge;
            } catch (Exception $e) {
                $this->logError("Ошибка при создании значка", [
                    'name' => $data['name'] ?? 'не указано'
                ], $e);

                throw new Exception("Не удалось создать значок: " . $e->getMessage());
            }
        });
    }

    /**
     * Обновить значок
     *
     * @param int $id ID значка
     * @param array $data Данные для обновления
     * @return Badge
     * @throws Exception
     */
    public function updateBadge($id, array $data)
    {
        $this->logInfo("Обновление значка", ['badge_id' => $id]);

        return $this->transaction(function () use ($id, $data) {
            try {
                $badge = Badge::query()->findOrFail($id);
                $badge->update($data);

                // Сбрасываем кеш
                $this->forgetCache($this->buildCacheKey('badge', [$id]));
                $this->forgetCache($this->buildCacheKey('all_badges'));

                $this->logInfo("Значок успешно обновлен", ['badge_id' => $id]);

                return $badge;
            } catch (Exception $e) {
                $this->logError("Ошибка при обновлении значка", ['badge_id' => $id], $e);
                throw new Exception("Не удалось обновить значок: " . $e->getMessage());
            }
        });
    }

    /**
     * Удалить значок
     *
     * @param int $id ID значка
     * @return bool
     * @throws Exception
     */
    public function deleteBadge($id)
    {
        $this->logInfo("Удаление значка", ['badge_id' => $id]);

        return $this->transaction(function () use ($id) {
            try {
                $badge = Badge::query()->findOrFail($id);

                $result = $badge->delete();

                // Сбрасываем кеш
                $this->forgetCache($this->buildCacheKey('badge', [$id]));
                $this->forgetCache($this->buildCacheKey('all_badges'));

                $this->logInfo("Значок успешно удален", ['badge_id' => $id]);

                return $result;
            } catch (Exception $e) {
                $this->logError("Ошибка при удалении значка", ['badge_id' => $id], $e);
                throw new Exception("Не удалось удалить значок: " . $e->getMessage());
            }
        });
    }

    /**
     * Получить значки пользователя
     *
     * @param int $userId ID пользователя
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getUserBadges(int $userId)
    {
        $cacheKey = $this->buildCacheKey('user_badges', [$userId]);

        return $this->getFromCacheOrStore($cacheKey, $this->defaultCacheMinutes, function () use ($userId) {
            $this->logInfo("Получение значков пользователя", ['user_id' => $userId]);

            return Badge::query()
                ->whereHas('users', function ($query) use ($userId) {
                    $query->where('user_id', $userId);
                })
                ->get();
        });
    }

    /**
     * Присвоить значок пользователю
     *
     * @param int $badgeId ID значка
     * @param int $userId ID пользователя
     * @return bool
     * @throws Exception
     */
    public function assignBadgeToUser(int $badgeId, int $userId)
    {
        $this->logInfo("Присвоение значка пользователю", [
            'badge_id' => $badgeId,
            'user_id' => $userId
        ]);

        return $this->transaction(function () use ($badgeId, $userId) {
            try {
                $badge = Badge::query()->findOrFail($badgeId);

                $alreadyAssigned = $badge->users()->where('user_id', $userId)->exists();

                if ($alreadyAssigned) {
                    $this->logInfo("Значок уже присвоен пользователю", [
                        'badge_id' => $badgeId,
                        'user_id' => $userId
                    ]);

                    return true;
                }

                $badge->users()->attach($userId);

                // Сбрасываем кеш
                $this->forgetCache($this->buildCacheKey('user_badges', [$userId]));

                $this->logInfo("Значок успешно присвоен пользователю", [
                    'badge_id' => $badgeId,
                    'user_id' => $userId
                ]);

                return true;
            } catch (Exception $e) {
                $this->logError("Ошибка при присвоении значка пользователю", [
                    'badge_id' => $badgeId,
                    'user_id' => $userId
                ], $e);

                throw new Exception("Не удалось присвоить значок пользователю: " . $e->getMessage());
            }
        });
    }

    /**
     * Отозвать значок у пользователя
     *
     * @param int $badgeId ID значка
     * @param int $userId ID пользователя
     * @return bool
     * @throws Exception
     */
    public function revokeBadgeFromUser(int $badgeId, int $userId)
    {
        $this->logInfo("Отзыв значка у пользователя", [
            'badge_id' => $badgeId,
            'user_id' => $userId
        ]);

        return $this->transaction(function () use ($badgeId, $userId) {
            try {
                $badge = Badge::query()->findOrFail($badgeId);

                $badge->users()->detach($userId);

                // Сбрасываем кеш
                $this->forgetCache($this->buildCacheKey('user_badges', [$userId]));

                $this->logInfo("Значок успешно отозван у пользователя", [
                    'badge_id' => $badgeId,
                    'user_id' => $userId
                ]);

                return true;
            } catch (Exception $e) {
                $this->logError("Ошибка при отзыве значка у пользователя", [
                    'badge_id' => $badgeId,
                    'user_id' => $userId
                ], $e);

                throw new Exception("Не удалось отозвать значок у пользователя: " . $e->getMessage());
            }
        });
    }
}
