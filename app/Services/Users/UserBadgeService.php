<?php

namespace App\Services\Users;

use App\Models\Content\Badge;
use App\Models\Users\User;
use App\Models\Users\UserBadge;
use App\Services\Base\SimpleService;
use Exception;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;


/**
 * Сервис для работы со значками пользователей
 */
class UserBadgeService extends SimpleService
{
    /**
     * Префикс кеша
     *
     * @var string
     */
    protected string $cachePrefix = 'user_badge';

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
        $this->setLogPrefix('UserBadgeService');
    }

    /**
     * Получить все значки пользователей
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllUserBadges(): \Illuminate\Database\Eloquent\Collection
    {
        $cacheKey = $this->buildCacheKey('all');

        return $this->getFromCacheOrStore($cacheKey, $this->defaultCacheMinutes, function () {
            $this->logInfo('Получение всех значков пользователей');

            try {
                $userBadges = UserBadge::with('badge')->get();

                $this->logInfo('Получены все значки пользователей', [
                    'count' => $userBadges->count()
                ]);

                return $userBadges;
            } catch (Exception $e) {
                $this->logError('Ошибка при получении всех значков пользователей', [], $e);
                return collect();
            }
        });
    }

    /**
     * Создать значок пользователя
     *
     * @param array $data Данные для создания
     * @return UserBadge|null
     */
    public function createUserBadge(array $data): ?UserBadge
    {
        $this->logInfo('Создание значка пользователя', [
            'data' => $data
        ]);

        return $this->transaction(function () use ($data) {
            try {
                $user = Auth::user();
                if (!$user) {
                    $this->logWarning('Пользователь не авторизован при создании значка');
                    throw new BadRequestException('Пользователь не авторизован');
                }

                $badgeId = $data['badge_id'] ?? null;
                if (!$badgeId) {
                    $this->logWarning('Не указан ID значка при создании', ['data' => $data]);
                    throw new BadRequestException('Не указан ID значка');
                }

                // Проверка существования значка
                $badge = Badge::query()->find($badgeId);
                if (!$badge) {
                    $this->logWarning('Значок не найден', ['badge_id' => $badgeId]);
                    throw new BadRequestException('Значок не найден');
                }

                // Проверка наличия значка у пользователя
                if ($user->badges()->where('badge_id', $badgeId)->exists()) {
                    $this->logWarning('Значок уже добавлен пользователю', [
                        'user_id' => $user->id,
                        'badge_id' => $badgeId
                    ]);
                    throw new BadRequestException('Значок уже добавлен пользователю');
                }

                // Привязка значка к пользователю
                $user->badges()->syncWithoutDetaching([$badgeId]);

                $userBadge = UserBadge::query()
                    ->where('user_id', $user->id)
                    ->where('badge_id', $badgeId)
                    ->first();

                // Очистка кеша
                $this->forgetCache([
                    $this->buildCacheKey('all'),
                    $this->buildCacheKey('user', [$user->id])
                ]);

                $this->logInfo('Создан значок пользователя', [
                    'user_id' => $user->id,
                    'badge_id' => $badgeId
                ]);

                return $userBadge;
            } catch (BadRequestException $e) {
                $this->logError('Ошибка запроса при создании значка пользователя', [
                    'data' => $data
                ], $e);
                throw $e;
            } catch (Exception $e) {
                $this->logError('Ошибка при создании значка пользователя', [
                    'data' => $data
                ], $e);
                return null;
            }
        });
    }

    /**
     * Получить значок пользователя по ID
     *
     * @param int $id ID значка пользователя
     * @return UserBadge|null
     */
    public function getUserBadgeById($id)
    {
        $cacheKey = $this->buildCacheKey('id', [$id]);

        return $this->getFromCacheOrStore($cacheKey, $this->defaultCacheMinutes, function () use ($id) {
            $this->logInfo('Получение значка пользователя по ID', ['id' => $id]);

            try {
                $user = Auth::user();
                if (!$user) {
                    $this->logWarning('Пользователь не авторизован при получении значка');
                    return null;
                }

                $userBadge = UserBadge::query()
                    ->where('id', $id)
                    ->where('user_id', $user->id)
                    ->with('badge')
                    ->first();

                if (!$userBadge) {
                    $this->logWarning('Значок пользователя не найден', ['id' => $id, 'user_id' => $user->id]);
                    return null;
                }

                $this->logInfo('Получен значок пользователя', ['id' => $id, 'user_id' => $user->id]);

                return $userBadge;
            } catch (Exception $e) {
                $this->logError('Ошибка при получении значка пользователя', ['id' => $id], $e);
                return null;
            }
        });
    }

    /**
     * Обновить значок пользователя
     *
     * @param int $id ID значка пользователя
     * @param array $data Данные для обновления
     * @return UserBadge|null
     * @throws BadRequestException
     * @throws ResourceNotFoundException|\Throwable
     */
    public function updateUserBadge($id, array $data)
    {
        $this->logInfo('Обновление значка пользователя', [
            'id' => $id,
            'data' => $data
        ]);

        return $this->transaction(function () use ($id, $data) {
            try {
                $user = Auth::user();
                if (!$user) {
                    $this->logWarning('Пользователь не авторизован при обновлении значка');
                    throw new BadRequestException('Пользователь не авторизован');
                }

                $userBadge = UserBadge::query()
                    ->where('id', $id)
                    ->where('user_id', $user->id)
                    ->first();

                if (!$userBadge) {
                    $this->logWarning('Значок пользователя не найден', ['id' => $id, 'user_id' => $user->id]);
                    throw new ResourceNotFoundException('Значок пользователя не найден');
                }

                if (isset($data['badge_id'])) {
                    $badgeId = $data['badge_id'];

                    // Проверка существования значка
                    $badge = Badge::query()->find($badgeId);
                    if (!$badge) {
                        $this->logWarning('Значок не найден', ['badge_id' => $badgeId]);
                        throw new BadRequestException('Значок не найден');
                    }

                    // Проверка наличия значка у пользователя
                    if ($user->badges()->where('badge_id', $badgeId)->where('user_badges.id', '<>', $id)->exists()) {
                        $this->logWarning('Значок уже добавлен пользователю', [
                            'user_id' => $user->id,
                            'badge_id' => $badgeId
                        ]);
                        throw new BadRequestException('Значок уже добавлен пользователю');
                    }
                }

                $userBadge->update($data);

                // Очистка кеша
                $this->forgetCache([
                    $this->buildCacheKey('all'),
                    $this->buildCacheKey('user', [$user->id]),
                    $this->buildCacheKey('id', [$id])
                ]);

                $this->logInfo('Обновлен значок пользователя', [
                    'id' => $id,
                    'user_id' => $user->id
                ]);

                return $userBadge->fresh();
            } catch (BadRequestException $e) {
                $this->logError('Ошибка запроса при обновлении значка пользователя', [
                    'id' => $id,
                    'data' => $data
                ], $e);
                throw $e;
            } catch (ResourceNotFoundException $e) {
                $this->logError('Ресурс не найден при обновлении значка пользователя', [
                    'id' => $id,
                    'data' => $data
                ], $e);
                throw $e;
            } catch (Exception $e) {
                $this->logError('Ошибка при обновлении значка пользователя', [
                    'id' => $id,
                    'data' => $data
                ], $e);
                return null;
            }
        });
    }

    /**
     * Установить активный значок для пользователя
     *
     * @param int $userId ID пользователя
     * @param int $badgeId ID значка
     * @return bool
     * @throws ResourceNotFoundException
     */
    public function setActiveBadgeForUser($userId, $badgeId)
    {
        $this->logInfo('Установка активного значка для пользователя', [
            'user_id' => $userId,
            'badge_id' => $badgeId
        ]);

        return $this->transaction(function () use ($userId, $badgeId) {
            try {
                $user = User::query()->find($userId);
                if (!$user) {
                    $this->logWarning('Пользователь не найден', ['user_id' => $userId]);
                    throw new ResourceNotFoundException('Пользователь не найден');
                }

                // Получаем текущий активный значок пользователя
                $currentActiveBadge = $user->badges()->wherePivot('is_active', true)->first();

                // Если есть активный значок, деактивируем его
                if ($currentActiveBadge) {
                    $user->badges()->updateExistingPivot($currentActiveBadge->id, ['is_active' => false]);
                }

                // Проверяем наличие значка у пользователя
                $userBadge = $user->badges()->where('badge_id', $badgeId)->first();
                if (!$userBadge) {
                    $this->logWarning('Значок не найден у пользователя', [
                        'user_id' => $userId,
                        'badge_id' => $badgeId
                    ]);
                    throw new ResourceNotFoundException('Значок не найден у пользователя');
                }

                // Устанавливаем значок как активный
                $user->badges()->updateExistingPivot($badgeId, ['is_active' => true]);

                // Очистка кеша
                $this->forgetCache([
                    $this->buildCacheKey('user', [$userId]),
                    $this->buildCacheKey('active', [$userId])
                ]);

                $this->logInfo('Активный значок установлен для пользователя', [
                    'user_id' => $userId,
                    'badge_id' => $badgeId
                ]);

                return true;
            } catch (ResourceNotFoundException $e) {
                $this->logError('Ресурс не найден при установке активного значка', [
                    'user_id' => $userId,
                    'badge_id' => $badgeId
                ], $e);
                throw $e;
            } catch (Exception $e) {
                $this->logError('Ошибка при установке активного значка', [
                    'user_id' => $userId,
                    'badge_id' => $badgeId
                ], $e);
                return false;
            }
        });
    }

    /**
     * Получить активный значок пользователя
     *
     * @param int $userId ID пользователя
     * @return UserBadge|null
     */
    public function getActiveBadgeForUser($userId)
    {
        $cacheKey = $this->buildCacheKey('active', [$userId]);

        return $this->getFromCacheOrStore($cacheKey, $this->defaultCacheMinutes, function () use ($userId) {
            $this->logInfo('Получение активного значка пользователя', ['user_id' => $userId]);

            try {
                $user = User::query()->find($userId);
                if (!$user) {
                    $this->logWarning('Пользователь не найден', ['user_id' => $userId]);
                    return null;
                }

                $activeBadge = $user->badges()->wherePivot('is_active', true)->first();

                if (!$activeBadge) {
                    $this->logInfo('Активный значок не найден у пользователя', ['user_id' => $userId]);
                    return null;
                }

                $this->logInfo('Получен активный значок пользователя', [
                    'user_id' => $userId,
                    'badge_id' => $activeBadge->id
                ]);

                return $activeBadge;
            } catch (Exception $e) {
                $this->logError('Ошибка при получении активного значка пользователя', [
                    'user_id' => $userId
                ], $e);
                return null;
            }
        });
    }

    /**
     * Удалить значок пользователя
     *
     * @param int $id ID значка пользователя
     * @return bool
     * @throws ResourceNotFoundException
     */
    public function deleteUserBadge($id)
    {
        $this->logInfo('Удаление значка пользователя', ['id' => $id]);

        return $this->transaction(function () use ($id) {
            try {
                $user = Auth::user();
                if (!$user) {
                    $this->logWarning('Пользователь не авторизован при удалении значка');
                    throw new BadRequestException('Пользователь не авторизован');
                }

                $userBadge = UserBadge::query()
                    ->where('id', $id)
                    ->where('user_id', $user->id)
                    ->first();

                if (!$userBadge) {
                    $this->logWarning('Значок пользователя не найден', ['id' => $id, 'user_id' => $user->id]);
                    throw new ResourceNotFoundException('Значок пользователя не найден');
                }

                $badgeId = $userBadge->badge_id;
                $result = $userBadge->delete();

                if ($result) {
                    // Очистка кеша
                    $this->forgetCache([
                        $this->buildCacheKey('all'),
                        $this->buildCacheKey('user', [$user->id]),
                        $this->buildCacheKey('id', [$id]),
                        $this->buildCacheKey('active', [$user->id])
                    ]);

                    $this->logInfo('Значок пользователя удален', [
                        'id' => $id,
                        'user_id' => $user->id,
                        'badge_id' => $badgeId
                    ]);
                } else {
                    $this->logWarning('Не удалось удалить значок пользователя', [
                        'id' => $id,
                        'user_id' => $user->id
                    ]);
                }

                return $result;
            } catch (BadRequestException $e) {
                $this->logError('Ошибка запроса при удалении значка пользователя', [
                    'id' => $id
                ], $e);
                throw $e;
            } catch (ResourceNotFoundException $e) {
                $this->logError('Ресурс не найден при удалении значка пользователя', [
                    'id' => $id
                ], $e);
                throw $e;
            } catch (Exception $e) {
                $this->logError('Ошибка при удалении значка пользователя', [
                    'id' => $id
                ], $e);
                return false;
            }
        });
    }

    /**
     * Получить все значки пользователя
     *
     * @param int $userId ID пользователя
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getUserBadges($userId)
    {
        $cacheKey = $this->buildCacheKey('user', [$userId]);

        return $this->getFromCacheOrStore($cacheKey, $this->defaultCacheMinutes, function () use ($userId) {
            $this->logInfo('Получение значков пользователя', ['user_id' => $userId]);

            try {
                $user = User::query()->find($userId);
                if (!$user) {
                    $this->logWarning('Пользователь не найден', ['user_id' => $userId]);
                    return collect();
                }

                $badges = $user->badges()->get();

                $this->logInfo('Получены значки пользователя', [
                    'user_id' => $userId,
                    'count' => $badges->count()
                ]);

                return $badges;
            } catch (Exception $e) {
                $this->logError('Ошибка при получении значков пользователя', [
                    'user_id' => $userId
                ], $e);
                return collect();
            }
        });
    }
}
