<?php

namespace App\Services\Content;

use App\Models\Content\Source;
use App\Repositories\SourceRepository;
use App\Services\Base\SimpleService;
use Exception;

class SourceService extends SimpleService
{
    /**
     * Репозиторий источников
     *
     * @var SourceRepository
     */
    private SourceRepository $sourceRepository;
    
    /**
     * Префикс кеша
     *
     * @var string
     */
    protected string $cachePrefix = 'source';

    /**
     * Время хранения кеша в минутах
     *
     * @var int
     */
    protected int $defaultCacheMinutes = 120;

    /**
     * Конструктор
     *
     * @param SourceRepository $sourceRepository
     */
    public function __construct(SourceRepository $sourceRepository)
    {
        parent::__construct();
        $this->setLogPrefix('SourceService');
        $this->sourceRepository = $sourceRepository;
    }

    /**
     * Получить все источники
     *
     * @return \Illuminate\Database\Eloquent\Collection
     * @throws Exception
     */
    public function getAllSources()
    {
        $cacheKey = $this->buildCacheKey('all_sources');
        
        return $this->getFromCacheOrStore($cacheKey, $this->defaultCacheMinutes, function () {
            $this->logInfo("Получение всех источников");
            
            try {
                return $this->sourceRepository->getAllSources();
            } catch (Exception $e) {
                $this->logError("Ошибка при получении всех источников", [], $e);
                throw new Exception("Не удалось получить список источников: " . $e->getMessage());
            }
        });
    }

    /**
     * Получить источник по ID
     *
     * @param int $id ID источника
     * @return Source
     * @throws Exception
     */
    public function getSourceById($id)
    {
        $cacheKey = $this->buildCacheKey('source', [$id]);
        
        return $this->getFromCacheOrStore($cacheKey, $this->defaultCacheMinutes, function () use ($id) {
            $this->logInfo("Получение источника по ID", ['source_id' => $id]);
            
            try {
                return $this->sourceRepository->getSourceById($id);
            } catch (Exception $e) {
                $this->logWarning("Источник не найден", ['source_id' => $id], $e);
                throw new Exception("Не удалось получить источник: " . $e->getMessage());
            }
        });
    }

    /**
     * Создать новый источник
     *
     * @param array $data Данные источника
     * @return Source
     * @throws Exception
     */
    public function createSource(array $data)
    {
        $this->logInfo("Создание нового источника", ['name' => $data['name'] ?? 'не указано']);
        
        return $this->transaction(function () use ($data) {
            try {
                $source = $this->sourceRepository->createSource($data);
                
                // Сбрасываем кеш
                $this->forgetCache($this->buildCacheKey('all_sources'));
                
                $this->logInfo("Источник успешно создан", ['source_id' => $source->id]);
                
                return $source;
            } catch (Exception $e) {
                $this->logError("Ошибка при создании источника", [
                    'name' => $data['name'] ?? 'не указано'
                ], $e);
                
                throw new Exception("Не удалось создать источник: " . $e->getMessage());
            }
        });
    }

    /**
     * Обновить источник
     *
     * @param int $id ID источника
     * @param array $data Данные для обновления
     * @return Source
     * @throws Exception
     */
    public function updateSource(int $id, array $data)
    {
        $this->logInfo("Обновление источника", ['source_id' => $id]);
        
        return $this->transaction(function () use ($id, $data) {
            try {
                $source = $this->sourceRepository->updateSource($id, $data);
                
                // Сбрасываем кеш
                $this->forgetCache($this->buildCacheKey('source', [$id]));
                $this->forgetCache($this->buildCacheKey('all_sources'));
                
                $this->logInfo("Источник успешно обновлен", ['source_id' => $id]);
                
                return $source;
            } catch (Exception $e) {
                $this->logError("Ошибка при обновлении источника", ['source_id' => $id], $e);
                throw new Exception("Не удалось обновить источник: " . $e->getMessage());
            }
        });
    }

    /**
     * Удалить источник
     *
     * @param int $id ID источника
     * @return bool
     * @throws Exception
     */
    public function deleteSource($id)
    {
        $this->logInfo("Удаление источника", ['source_id' => $id]);
        
        return $this->transaction(function () use ($id) {
            try {
                $result = $this->sourceRepository->deleteSource($id);
                
                // Сбрасываем кеш
                $this->forgetCache($this->buildCacheKey('source', [$id]));
                $this->forgetCache($this->buildCacheKey('all_sources'));
                
                $this->logInfo("Источник успешно удален", ['source_id' => $id]);
                
                return $result;
            } catch (Exception $e) {
                $this->logError("Ошибка при удалении источника", ['source_id' => $id], $e);
                throw new Exception("Не удалось удалить источник: " . $e->getMessage());
            }
        });
    }
    
    /**
     * Получить источники с постами
     *
     * @param int $limit Лимит источников
     * @return \Illuminate\Database\Eloquent\Collection
     * @throws Exception
     */
    public function getSourcesWithPosts(int $limit = 10)
    {
        $cacheKey = $this->buildCacheKey('sources_with_posts', [$limit]);
        
        return $this->getFromCacheOrStore($cacheKey, $this->defaultCacheMinutes, function () use ($limit) {
            $this->logInfo("Получение источников с постами", ['limit' => $limit]);
            
            try {
                return Source::query()
                    ->withCount('posts')
                    ->having('posts_count', '>', 0)
                    ->orderBy('posts_count', 'desc')
                    ->limit($limit)
                    ->get();
            } catch (Exception $e) {
                $this->logError("Ошибка при получении источников с постами", ['limit' => $limit], $e);
                throw new Exception("Не удалось получить источники с постами: " . $e->getMessage());
            }
        });
    }
    
    /**
     * Поиск источников по имени
     *
     * @param string $query Поисковый запрос
     * @return \Illuminate\Database\Eloquent\Collection
     * @throws Exception
     */
    public function searchSources(string $query)
    {
        $cacheKey = $this->buildCacheKey('search_sources', [$query]);
        
        return $this->getFromCacheOrStore($cacheKey, 30, function () use ($query) {
            $this->logInfo("Поиск источников", ['query' => $query]);
            
            try {
                return Source::query()
                    ->where('name', 'like', "%{$query}%")
                    ->orWhere('description', 'like', "%{$query}%")
                    ->get();
            } catch (Exception $e) {
                $this->logError("Ошибка при поиске источников", ['query' => $query], $e);
                throw new Exception("Не удалось выполнить поиск источников: " . $e->getMessage());
            }
        });
    }
}
