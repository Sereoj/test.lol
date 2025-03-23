<?php

namespace App\Services\Posts;

use App\Models\Content\Tag;
use App\Models\Posts\Post;
use App\Models\Users\User;
use App\Services\Base\SimpleService;
use App\Utils\TextUtil;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Builder;

class PostSearchService extends SimpleService
{
    /**
     * Префикс кеша
     *
     * @var string
     */
    protected string $cachePrefix = 'post_search';

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
        $this->setLogPrefix('PostSearchService');
    }

    /**
     * Основной метод для выполнения поиска
     *
     * @param string $query Поисковый запрос
     * @return array
     * @throws Exception
     */
    public function search(string $query)
    {
        $this->logInfo("Выполнение поиска", ['query' => $query]);

        $cacheKey = $this->buildCacheKey('search_results', [md5($query)]);

        return $this->getFromCacheOrStore($cacheKey, 15, function () use ($query) {
            try {
                // Подготовка вариантов поискового запроса
                $queries = $this->prepareSearchQueries($query);

                if (empty($queries)) {
                    $this->logWarning("Пустой поисковый запрос", ['query' => $query]);
                    return [
                        'posts' => collect(),
                        'tags' => collect(),
                        'users' => collect(),
                    ];
                }

                // Выполнение поиска
                $results = [
                    'posts' => $this->searchPosts($queries),
                    'tags' => $this->searchTags($queries),
                    'users' => $this->searchUsers($queries),
                ];

                $this->logInfo("Результаты поиска", [
                    'query' => $query,
                    'posts_count' => $results['posts']->count(),
                    'tags_count' => $results['tags']->count(),
                    'users_count' => $results['users']->count(),
                ]);

                return $results;
            } catch (Exception $e) {
                $this->logError("Ошибка при выполнении поиска", ['query' => $query], $e);
                throw new Exception("Не удалось выполнить поиск: " . $e->getMessage());
            }
        });
    }

    /**
     * Подготовка вариантов поискового запроса
     *
     * @param string $query Поисковый запрос
     * @return array
     */
    public function prepareSearchQueries(string $query): array
    {
        $this->logInfo("Подготовка вариантов поискового запроса", ['query' => $query]);

        $cacheKey = $this->buildCacheKey('query_variants', [md5($query)]);

        return $this->getFromCacheOrStore($cacheKey, 60, function () use ($query) {
            try {
                $variants = TextUtil::generateVariants(str($query)->lower());

                // Убираем дубли и фильтруем пустые строки
                $cleanedVariants = array_unique(array_filter($variants));

                $this->logInfo("Сформированы варианты поискового запроса", [
                    'original' => $query,
                    'variants_count' => count($cleanedVariants)
                ]);

                return $cleanedVariants;
            } catch (Exception $e) {
                $this->logError("Ошибка при подготовке вариантов поискового запроса", [
                    'query' => $query
                ], $e);

                // В случае ошибки возвращаем исходный запрос
                return [$query];
            }
        });
    }

    /**
     * Поиск постов
     *
     * @param array $queries Варианты поискового запроса
     * @return \Illuminate\Support\Collection
     * @throws Exception
     */
    public function searchPosts(array $queries)
    {
        $this->logInfo("Поиск постов", ['queries_count' => count($queries)]);

        // Проверяем, что массив запросов не пуст
        if (empty($queries)) {
            $this->logWarning("Пустой массив поисковых запросов для постов");
            return collect();
        }

        try {
            $baseQuery = Post::query();

            $relevanceCase = '
                CASE
                    WHEN title = ? THEN 100
                    WHEN title LIKE ? THEN 75
                    WHEN content LIKE ? THEN 75
                    WHEN slug LIKE ? THEN 50
                    WHEN title LIKE ? THEN 50
                    WHEN content LIKE ? THEN 50
                    ELSE 25
                END as relevance_score
            ';

            $bindParams = [
                $queries[0],          // Точное совпадение для title
                $queries[0].'%',      // Начинается с для title
                $queries[0].'%',      // Начинается с (в контенте)
                $queries[0].'%',      // Начинается с для slug
                '%'.$queries[0].'%',  // Содержит для title
                '%'.$queries[0].'%',  // Содержит для content
            ];

            $whereConditions = [];
            foreach ($queries as $q) {
                $whereConditions[] = '(title LIKE ? OR content LIKE ? OR slug LIKE ?)';
                $bindParams[] = '%'.$q.'%'; // Для title
                $bindParams[] = '%'.$q.'%'; // Для content
                $bindParams[] = '%'.$q.'%'; // Для slug
            }

            $results = $baseQuery
                ->selectRaw("*, $relevanceCase")
                ->whereRaw(implode(' OR ', $whereConditions), $bindParams)
                ->whereNull('deleted_at')
                ->orderByRaw('relevance_score DESC')
                ->limit(5)
                ->get();

            $this->logInfo("Найдено постов", ['count' => $results->count()]);

            return $results;
        } catch (Exception $e) {
            $this->logError("Ошибка при поиске постов", [], $e);
            throw new Exception("Не удалось выполнить поиск постов: " . $e->getMessage());
        }
    }

    /**
     * Поиск тегов
     *
     * @param array $queries Варианты поискового запроса
     * @return Collection
     * @throws Exception
     */
    protected function searchTags(array $queries): Collection
    {
        $this->logInfo("Поиск тегов", ['queries_count' => count($queries)]);

        try {
            $results = $this->performSearch(Tag::query(), $queries, ['name', 'slug']);

            $this->logInfo("Найдено тегов", ['count' => $results->count()]);

            return $results;
        } catch (Exception $e) {
            $this->logError("Ошибка при поиске тегов", [], $e);
            throw new Exception("Не удалось выполнить поиск тегов: " . $e->getMessage());
        }
    }

    /**
     * Поиск пользователей
     *
     * @param array $queries Варианты поискового запроса
     * @return Collection
     * @throws Exception
     */
    protected function searchUsers(array $queries): Collection
    {
        $this->logInfo("Поиск пользователей", ['queries_count' => count($queries)]);

        try {
            $results = $this->performSearch(User::query(), $queries, ['username', 'name', 'email']);

            $this->logInfo("Найдено пользователей", ['count' => $results->count()]);

            return $results;
        } catch (Exception $e) {
            $this->logError("Ошибка при поиске пользователей", [], $e);
            throw new Exception("Не удалось выполнить поиск пользователей: " . $e->getMessage());
        }
    }

    /**
     * Общий метод для выполнения поиска
     *
     * @param Builder $queryBuilder Построитель запроса
     * @param array $queries Варианты поискового запроса
     * @param array $fields Поля для поиска
     * @return \Illuminate\Support\Collection
     */
    protected function performSearch($queryBuilder, array $queries, array $fields): \Illuminate\Support\Collection
    {
        if (empty($queries)) {
            return collect();
        }

        $queryBuilder->where(function ($q) use ($queries, $fields) {
            $this->buildSearchConditions($q, $queries, $fields);
        });

        return $queryBuilder->limit(5)->get();
    }

    /**
     * Построение условий поиска
     *
     * @param Builder $query Построитель запроса
     * @param array $queries Варианты поискового запроса
     * @param array $fields Поля для поиска
     * @return void
     */
    protected function buildSearchConditions($query, array $queries, array $fields): void
    {
        foreach ($queries as $queryText) {
            foreach ($fields as $field) {
                $query->orWhere($field, 'LIKE', '%' . $queryText . '%');
            }
        }
    }
}
