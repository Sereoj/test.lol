<?php

namespace App\Services\Users;

use App\Models\Users\UserLevel;
use App\Services\Base\SimpleService;
use Exception;

/**
 * Сервис для управления уровнями пользователей
 */
class UserLevelService extends SimpleService
{
    /**
     * Префикс кеша
     *
     * @var string
     */
    protected string $cachePrefix = 'user_level';
    
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
        $this->setLogPrefix('UserLevelService');
    }
    
    /**
     * Создание нового уровня.
     *
     * @param array $name Название уровня (многоязычный массив)
     * @param int $experienceRequired Требуемый опыт для достижения уровня
     * @return UserLevel|null
     */
    public function createLevel($name, $experienceRequired)
    {
        $this->logInfo('Создание нового уровня', [
            'name' => $name,
            'experience_required' => $experienceRequired
        ]);
        
        return $this->transaction(function () use ($name, $experienceRequired) {
            try {
                $level = UserLevel::query()->create([
                    'name' => json_encode($name),
                    'experience_required' => $experienceRequired,
                ]);
                
                // Очистка кеша всех уровней
                $this->forgetCache($this->buildCacheKey('all'));
                
                $this->logInfo('Создан новый уровень', [
                    'id' => $level->id,
                    'experience_required' => $level->experience_required
                ]);
                
                return $level;
            } catch (Exception $e) {
                $this->logError('Ошибка при создании нового уровня', [
                    'name' => $name,
                    'experience_required' => $experienceRequired
                ], $e);
                
                return null;
            }
        });
    }

    /**
     * Присваивание уровня пользователю.
     *
     * @param \App\Models\Users\User $user Пользователь
     * @param int $levelId ID уровня
     * @return bool Результат операции
     */
    public function assignLevelToUser($user, $levelId)
    {
        $this->logInfo('Присваивание уровня пользователю', [
            'user_id' => $user->id,
            'level_id' => $levelId
        ]);
        
        return $this->transaction(function () use ($user, $levelId) {
            try {
                $level = UserLevel::query()->find($levelId);
                
                if (!$level) {
                    $this->logWarning('Уровень не найден при присваивании пользователю', [
                        'user_id' => $user->id,
                        'level_id' => $levelId
                    ]);
                    return false;
                }
                
                $user->level()->associate($level);
                $result = $user->save();
                
                if ($result) {
                    $this->logInfo('Уровень успешно присвоен пользователю', [
                        'user_id' => $user->id,
                        'level_id' => $levelId
                    ]);
                } else {
                    $this->logWarning('Не удалось присвоить уровень пользователю', [
                        'user_id' => $user->id,
                        'level_id' => $levelId
                    ]);
                }
                
                return $result;
            } catch (Exception $e) {
                $this->logError('Ошибка при присваивании уровня пользователю', [
                    'user_id' => $user->id,
                    'level_id' => $levelId
                ], $e);
                
                return false;
            }
        });
    }

    /**
     * Получить уровень по ID.
     *
     * @param int $id ID уровня
     * @return UserLevel|null
     */
    public function getLevelById($id)
    {
        $cacheKey = $this->buildCacheKey('level', [$id]);
        
        return $this->getFromCacheOrStore($cacheKey, $this->defaultCacheMinutes, function () use ($id) {
            $this->logInfo('Получение уровня по ID', ['id' => $id]);
            
            try {
                $level = UserLevel::query()->find($id);
                
                if (!$level) {
                    $this->logWarning('Уровень не найден', ['id' => $id]);
                    return null;
                }
                
                $this->logInfo('Уровень получен', ['id' => $id]);
                
                return $level;
            } catch (Exception $e) {
                $this->logError('Ошибка при получении уровня', ['id' => $id], $e);
                return null;
            }
        });
    }

    /**
     * Получить все уровни.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllLevels()
    {
        $cacheKey = $this->buildCacheKey('all');
        
        return $this->getFromCacheOrStore($cacheKey, $this->defaultCacheMinutes, function () {
            $this->logInfo('Получение всех уровней');
            
            try {
                $levels = UserLevel::all();
                
                $this->logInfo('Получены все уровни', ['count' => $levels->count()]);
                
                return $levels;
            } catch (Exception $e) {
                $this->logError('Ошибка при получении всех уровней', [], $e);
                return collect();
            }
        });
    }
    
    /**
     * Обновить уровень
     * 
     * @param int $id ID уровня
     * @param array $data Данные для обновления
     * @return UserLevel|null
     */
    public function updateLevel($id, array $data)
    {
        $this->logInfo('Обновление уровня', ['id' => $id, 'data' => $data]);
        
        return $this->transaction(function () use ($id, $data) {
            try {
                $level = UserLevel::query()->find($id);
                
                if (!$level) {
                    $this->logWarning('Уровень не найден при обновлении', ['id' => $id]);
                    return null;
                }
                
                $level->update($data);
                
                // Очистка кеша
                $this->forgetCache([
                    $this->buildCacheKey('all'),
                    $this->buildCacheKey('level', [$id])
                ]);
                
                $this->logInfo('Уровень обновлен', ['id' => $id]);
                
                return $level;
            } catch (Exception $e) {
                $this->logError('Ошибка при обновлении уровня', ['id' => $id, 'data' => $data], $e);
                return null;
            }
        });
    }
    
    /**
     * Удалить уровень
     * 
     * @param int $id ID уровня
     * @return bool
     */
    public function deleteLevel($id)
    {
        $this->logInfo('Удаление уровня', ['id' => $id]);
        
        return $this->transaction(function () use ($id) {
            try {
                $level = UserLevel::query()->find($id);
                
                if (!$level) {
                    $this->logWarning('Уровень не найден при удалении', ['id' => $id]);
                    return false;
                }
                
                $result = $level->delete();
                
                if ($result) {
                    // Очистка кеша
                    $this->forgetCache([
                        $this->buildCacheKey('all'),
                        $this->buildCacheKey('level', [$id])
                    ]);
                    
                    $this->logInfo('Уровень удален', ['id' => $id]);
                } else {
                    $this->logWarning('Не удалось удалить уровень', ['id' => $id]);
                }
                
                return $result;
            } catch (Exception $e) {
                $this->logError('Ошибка при удалении уровня', ['id' => $id], $e);
                return false;
            }
        });
    }
}
