<?php

namespace App\Services\Posts;

use App\Models\Categories\Category;
use App\Models\Content\Tag;
use App\Models\Posts\Post;
use App\Models\Users\User;
use App\Services\API\LibreTranslateService;
use App\Services\Base\SimpleService;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Сервис для поисковых подсказок
 */
class SearchSuggestionService extends SimpleService
{
    /**
     * Префикс кеша
     *
     * @var string
     */
    protected string $cachePrefix = 'search_suggestion';

    /**
     * Время хранения кеша в минутах
     *
     * @var int
     */
    protected int $defaultCacheMinutes = 30;

    /**
     * Конструктор
     */
    public function __construct()
    {
        parent::__construct();
        $this->setLogPrefix('SearchSuggestionService');
    }

    /**
     * Получить предложения поиска на основе запроса
     *
     * @param string $query Поисковый запрос
     * @param int $limit Лимит результатов
     * @return Collection
     * @throws Exception
     */
    public function suggest($query, $limit = 10)
    {
        $this->logInfo("Получение предложений для поиска", [
            'query' => $query,
            'limit' => $limit
        ]);

        $cacheKey = $this->buildCacheKey('suggestions', [md5($query), $limit]);

        return $this->getFromCacheOrStore($cacheKey, 5, function () use ($query, $limit) {
            try {
                // Готовим запросы для поиска
                $searchService = new PostSearchService();
                $queries = $searchService->prepareSearchQueries($query);

                $translatedQuery = $this->translateQuery($query);
                if ($translatedQuery !== $query) {
                    $this->logInfo("Запрос был переведен", [
                        'original' => $query,
                        'translated' => $translatedQuery
                    ]);
                    $queries[] = $translatedQuery;
                }

                $suggestions = collect();

                foreach ($queries as $preparedQuery) {
                    $this->logInfo("Поиск предложений для подзапроса", ['query' => $preparedQuery]);

                    // Посты
                    $postSuggestions = $this->getPostSuggestions($preparedQuery, $limit);

                    // Пользователи
                    $userSuggestions = $this->getUserSuggestions($preparedQuery, $limit);

                    // Теги
                    $tagSuggestions = $this->getTagSuggestions($preparedQuery, $limit);

                    // Категории
                    $categorySuggestions = $this->getCategorySuggestions($preparedQuery, $limit);

                    // Объединяем все предложения
                    $suggestions = $suggestions->merge($postSuggestions)
                        ->merge($userSuggestions)
                        ->merge($tagSuggestions)
                        ->merge($categorySuggestions);
                }

                // Сортируем по релевантности и ограничиваем количество результатов
                $suggestions = $suggestions->unique('text')
                    ->sortByDesc('relevance_score')
                    ->take($limit)
                    ->values();

                $this->logInfo("Предложения для поиска сформированы", [
                    'count' => $suggestions->count()
                ]);

                return $suggestions;
            } catch (Exception $e) {
                $this->logError("Ошибка при получении предложений для поиска", [
                    'query' => $query
                ], $e);

                throw new Exception("Не удалось получить предложения для поиска: " . $e->getMessage());
            }
        });
    }

    /**
     * Получить предложения из постов
     *
     * @param string $query Поисковый запрос
     * @param int $limit Лимит результатов
     * @return Collection
     */
    protected function getPostSuggestions(string $query, int $limit): Collection
    {
        return Post::query()
            ->published()
            ->where('title', 'like', "%{$query}%")
            ->select('title as text', DB::raw("'post' as type"), DB::raw('100 as relevance_score'))
            ->take($limit)
            ->get();
    }

    /**
     * Получить предложения из пользователей
     *
     * @param string $query Поисковый запрос
     * @param int $limit Лимит результатов
     * @return Collection
     */
    protected function getUserSuggestions(string $query, int $limit): Collection
    {
        return User::query()
            ->where('username', 'like', "%{$query}%")
            ->orWhere('email', 'like', "%{$query}%")
            ->select('username as text', DB::raw("'user' as type"), DB::raw('75 as relevance_score'))
            ->take($limit)
            ->get();
    }

    /**
     * Получить предложения из тегов
     *
     * @param string $query Поисковый запрос
     * @param int $limit Лимит результатов
     * @return Collection
     */
    protected function getTagSuggestions(string $query, int $limit): Collection
    {
        return Tag::query()
            ->where('name', 'like', "%{$query}%")
            ->select('name as text', DB::raw("'tag' as type"), DB::raw('50 as relevance_score'))
            ->take($limit)
            ->get();
    }

    /**
     * Получить предложения из категорий
     *
     * @param string $query Поисковый запрос
     * @param int $limit Лимит результатов
     * @return Collection
     */
    protected function getCategorySuggestions(string $query, int $limit): Collection
    {
        return Category::query()
            ->where('name', 'like', "%{$query}%")
            ->select('name as text', DB::raw("'category' as type"), DB::raw('25 as relevance_score'))
            ->take($limit)
            ->get();
    }

    /**
     * Метод для перевода текста
     *
     * @param string $query Поисковый запрос
     * @return string
     */
    protected function translateQuery(string $query): string
    {
        $cacheKey = $this->buildCacheKey('translation', [md5($query)]);

        return $this->getFromCacheOrStore($cacheKey, 60, function () use ($query) {
            $this->logInfo("Перевод поискового запроса", ['query' => $query]);

            try {
                $translatedQuery = LibreTranslateService::translateText($query, 'ru', 'en');

                if ($translatedQuery) {
                    $this->logInfo("Запрос успешно переведен", [
                        'original' => $query,
                        'translated' => $translatedQuery
                    ]);

                    return $translatedQuery;
                }

                $this->logWarning("Не удалось перевести запрос", ['query' => $query]);
                return $query;
            } catch (Exception $e) {
                $this->logWarning("Ошибка при переводе запроса", ['query' => $query], $e);
                return $query;
            }
        });
    }

    /**
     * Создать новую запись
     *
     * @param array $data
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function create(array $data): ?\Illuminate\Database\Eloquent\Model
    {
        $this->logWarning('Метод create не реализован в SearchSuggestionService');
        return null;
    }

    /**
     * Обновить запись
     *
     * @param int $id
     * @param array $data
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function update(int $id, array $data): ?\Illuminate\Database\Eloquent\Model
    {
        $this->logWarning('Метод update не реализован в SearchSuggestionService');
        return null;
    }

    /**
     * Удалить запись
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        $this->logWarning('Метод delete не реализован в SearchSuggestionService');
        return false;
    }

    /**
     * Найти запись по ID
     *
     * @param int $id
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function findById(int $id): ?\Illuminate\Database\Eloquent\Model
    {
        $this->logWarning('Метод findById не реализован в SearchSuggestionService');
        return null;
    }

    /**
     * Получить все модели
     *
     * @param array $relations
     * @return \Illuminate\Support\Collection
     */
    protected function getAllModels(array $relations = []): \Illuminate\Support\Collection
    {
        return collect();
    }
}
