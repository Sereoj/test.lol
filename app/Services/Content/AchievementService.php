<?php

namespace App\Services\Content;

use App\Models\Content\Achievement;
use App\Models\Users\User;
use App\Services\Base\SimpleService;
use Exception;

class AchievementService extends SimpleService
{
    /**
     * Префикс кеша
     *
     * @var string
     */
    protected string $cachePrefix = 'achievement';

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
        $this->setLogPrefix('AchievementService');
    }

    /**
     * Получить все достижения
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllAchievements()
    {
        $cacheKey = $this->buildCacheKey('all_achievements');
        
        return $this->getFromCacheOrStore($cacheKey, $this->defaultCacheMinutes, function () {
            $this->logInfo("Получение всех достижений");
            return Achievement::all();
        });
    }

    /**
     * Получить достижение по ID
     *
     * @param int $id ID достижения
     * @return Achievement
     * @throws Exception
     */
    public function getAchievementById(int $id)
    {
        $cacheKey = $this->buildCacheKey('achievement', [$id]);
        
        return $this->getFromCacheOrStore($cacheKey, $this->defaultCacheMinutes, function () use ($id) {
            $this->logInfo("Получение достижения по ID", ['achievement_id' => $id]);
            
            try {
                return Achievement::findOrFail($id);
            } catch (Exception $e) {
                $this->logWarning("Достижение не найдено", ['achievement_id' => $id]);
                throw new Exception("Достижение с ID {$id} не найдено");
            }
        });
    }

    /**
     * Получить достижения пользователя
     *
     * @param User $user Пользователь
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getUserAchievements(User $user)
    {
        $cacheKey = $this->buildCacheKey('user_achievements', [$user->id]);
        
        return $this->getFromCacheOrStore($cacheKey, $this->defaultCacheMinutes, function () use ($user) {
            $this->logInfo("Получение достижений пользователя", ['user_id' => $user->id]);
            return $user->achievements;
        });
    }

    /**
     * Создать новое достижение
     *
     * @param array $data Данные достижения
     * @return Achievement
     * @throws Exception
     */
    public function createAchievement(array $data)
    {
        $this->logInfo("Создание нового достижения", ['name' => $data['name'] ?? 'не указано']);
        
        return $this->transaction(function () use ($data) {
            try {
                $achievement = Achievement::create($data);
                
                // Сбрасываем кеш
                $this->forgetCache($this->buildCacheKey('all_achievements'));
                
                $this->logInfo("Достижение успешно создано", ['achievement_id' => $achievement->id]);
                
                return $achievement;
            } catch (Exception $e) {
                $this->logError("Ошибка при создании достижения", [
                    'name' => $data['name'] ?? 'не указано'
                ], $e);
                
                throw new Exception("Не удалось создать достижение: " . $e->getMessage());
            }
        });
    }

    /**
     * Присвоить достижение пользователю
     *
     * @param User $user Пользователь
     * @param Achievement $achievement Достижение
     * @return bool
     * @throws Exception
     */
    public function assignAchievementToUser(User $user, Achievement $achievement)
    {
        $this->logInfo("Присвоение достижения пользователю", [
            'user_id' => $user->id,
            'achievement_id' => $achievement->id
        ]);
        
        return $this->transaction(function () use ($user, $achievement) {
            try {
                if (!$user->achievements->contains($achievement)) {
                    $user->achievements()->attach($achievement);
                    
                    // Сбрасываем кеш
                    $this->forgetCache($this->buildCacheKey('user_achievements', [$user->id]));
                    
                    $this->logInfo("Достижение успешно присвоено пользователю", [
                        'user_id' => $user->id,
                        'achievement_id' => $achievement->id
                    ]);
                    
                    return true;
                }
                
                $this->logInfo("Достижение уже присвоено пользователю", [
                    'user_id' => $user->id,
                    'achievement_id' => $achievement->id
                ]);
                
                return false;
            } catch (Exception $e) {
                $this->logError("Ошибка при присвоении достижения пользователю", [
                    'user_id' => $user->id,
                    'achievement_id' => $achievement->id
                ], $e);
                
                throw new Exception("Не удалось присвоить достижение пользователю: " . $e->getMessage());
            }
        });
    }

    /**
     * Удалить достижение у пользователя
     *
     * @param User $user Пользователь
     * @param Achievement $achievement Достижение
     * @return bool
     * @throws Exception
     */
    public function removeAchievementFromUser(User $user, Achievement $achievement)
    {
        $this->logInfo("Удаление достижения у пользователя", [
            'user_id' => $user->id,
            'achievement_id' => $achievement->id
        ]);
        
        return $this->transaction(function () use ($user, $achievement) {
            try {
                if ($user->achievements->contains($achievement)) {
                    $user->achievements()->detach($achievement);
                    
                    // Сбрасываем кеш
                    $this->forgetCache($this->buildCacheKey('user_achievements', [$user->id]));
                    
                    $this->logInfo("Достижение успешно удалено у пользователя", [
                        'user_id' => $user->id,
                        'achievement_id' => $achievement->id
                    ]);
                    
                    return true;
                }
                
                $this->logInfo("Достижение не было присвоено пользователю", [
                    'user_id' => $user->id,
                    'achievement_id' => $achievement->id
                ]);
                
                return false;
            } catch (Exception $e) {
                $this->logError("Ошибка при удалении достижения у пользователя", [
                    'user_id' => $user->id,
                    'achievement_id' => $achievement->id
                ], $e);
                
                throw new Exception("Не удалось удалить достижение у пользователя: " . $e->getMessage());
            }
        });
    }
}
