<?php

namespace App\Services\Users;

use App\Exceptions\ResourceNotFoundException;
use App\Models\Sources\Source;
use App\Models\Users\User;
use App\Services\Base\SimpleService;
use Exception;
use Illuminate\Support\Facades\DB;

/**
 * Сервис для работы с источниками пользователей
 */
class UserSourceService extends SimpleService
{
    /**
     * Префикс кеша
     *
     * @var string
     */
    protected string $cachePrefix = 'user_source';
    
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
        $this->setLogPrefix('UserSourceService');
    }

    /**
     * Добавить источник к пользователю
     *
     * @param User $user Пользователь
     * @param int $sourceId ID источника
     * @return bool Результат операции
     * @throws ResourceNotFoundException
     */
    public function addSourceToUser($user, $sourceId)
    {
        $this->logInfo('Добавление источника пользователю', [
            'user_id' => $user->id,
            'source_id' => $sourceId
        ]);
        
        return $this->transaction(function () use ($user, $sourceId) {
            try {
                // Проверка существования источника
                $source = Source::query()->find($sourceId);
                if (!$source) {
                    $this->logWarning('Источник не найден', ['source_id' => $sourceId]);
                    throw new ResourceNotFoundException('Источник не найден');
                }
                
                // Добавление источника к пользователю
                if ($user->sources()->where('source_id', $sourceId)->exists()) {
                    $this->logInfo('Источник уже добавлен пользователю', [
                        'user_id' => $user->id,
                        'source_id' => $sourceId
                    ]);
                    return true;
                }
                
                // Проверка наличия других источников
                $hasOtherSources = $user->sources()->exists();
                
                // Привязка источника к пользователю
                $user->sources()->syncWithoutDetaching([$sourceId]);
                
                // Очистка кеша
                $this->forgetCache($this->buildCacheKey('user', [$user->id]));
                
                $this->logInfo('Источник успешно добавлен пользователю', [
                    'user_id' => $user->id,
                    'source_id' => $sourceId,
                    'has_other_sources' => $hasOtherSources
                ]);
                
                return true;
            } catch (ResourceNotFoundException $e) {
                $this->logError('Ресурс не найден при добавлении источника пользователю', [
                    'user_id' => $user->id,
                    'source_id' => $sourceId
                ], $e);
                throw $e;
            } catch (Exception $e) {
                $this->logError('Ошибка при добавлении источника пользователю', [
                    'user_id' => $user->id,
                    'source_id' => $sourceId
                ], $e);
                return false;
            }
        });
    }

    /**
     * Удалить источник у пользователя
     *
     * @param int $userId ID пользователя
     * @param int $sourceId ID источника
     * @return bool Результат операции
     * @throws ResourceNotFoundException
     */
    public function removeSourceFromUser($userId, $sourceId)
    {
        $this->logInfo('Удаление источника у пользователя', [
            'user_id' => $userId,
            'source_id' => $sourceId
        ]);
        
        return $this->transaction(function () use ($userId, $sourceId) {
            try {
                $user = User::query()->find($userId);
                if (!$user) {
                    $this->logWarning('Пользователь не найден', ['user_id' => $userId]);
                    throw new ResourceNotFoundException('Пользователь не найден');
                }
                
                // Проверка существования источника у пользователя
                if (!$user->sources()->where('source_id', $sourceId)->exists()) {
                    $this->logWarning('Источник не найден у пользователя', [
                        'user_id' => $userId,
                        'source_id' => $sourceId
                    ]);
                    throw new ResourceNotFoundException('Источник не найден у пользователя');
                }
                
                // Удаление источника у пользователя
                $user->sources()->detach($sourceId);
                
                // Очистка кеша
                $this->forgetCache($this->buildCacheKey('user', [$userId]));
                
                $this->logInfo('Источник успешно удален у пользователя', [
                    'user_id' => $userId,
                    'source_id' => $sourceId
                ]);
                
                return true;
            } catch (ResourceNotFoundException $e) {
                $this->logError('Ресурс не найден при удалении источника у пользователя', [
                    'user_id' => $userId,
                    'source_id' => $sourceId
                ], $e);
                throw $e;
            } catch (Exception $e) {
                $this->logError('Ошибка при удалении источника у пользователя', [
                    'user_id' => $userId,
                    'source_id' => $sourceId
                ], $e);
                return false;
            }
        });
    }

    /**
     * Получить все источники пользователя
     *
     * @param int $userId ID пользователя
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getUserSources($userId)
    {
        $cacheKey = $this->buildCacheKey('user', [$userId]);
        
        return $this->getFromCacheOrStore($cacheKey, $this->defaultCacheMinutes, function () use ($userId) {
            $this->logInfo('Получение источников пользователя', ['user_id' => $userId]);
            
            try {
                $user = User::query()->find($userId);
                if (!$user) {
                    $this->logWarning('Пользователь не найден при получении источников', ['user_id' => $userId]);
                    return collect();
                }
                
                $sources = $user->sources()->get();
                
                $this->logInfo('Получены источники пользователя', [
                    'user_id' => $userId,
                    'count' => $sources->count()
                ]);
                
                return $sources;
            } catch (Exception $e) {
                $this->logError('Ошибка при получении источников пользователя', [
                    'user_id' => $userId
                ], $e);
                return collect();
            }
        });
    }
}
