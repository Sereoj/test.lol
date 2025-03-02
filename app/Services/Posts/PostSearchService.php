<?php

namespace App\Services\Posts;

use App\Models\Content\Tag;
use App\Models\Posts\Post;
use App\Models\Users\User;
use App\Utils\TextUtil;
use Illuminate\Support\Facades\Log;

class PostSearchService
{
    /**
     * Основной метод для выполнения поиска.
     */
    public function search(string $query)
    {
        // Подготовка вариантов поискового запроса
        $queries = $this->prepareSearchQueries($query);

        // Выполнение поиска
        return [
            'posts' => $this->searchPosts($queries),
            'tags' => $this->searchTags($queries),
            'users' => $this->searchUsers($queries),
        ];
    }

    /**
     * Подготовка вариантов поискового запроса.
     */
    public function prepareSearchQueries(string $query): array
    {
        $variants = TextUtil::generateVariants(str($query)->lower());

        // Убираем дубли и фильтруем пустые строки
        $cleanedVariants = array_unique(array_filter($variants));

        foreach ($variants as $key => $value) {
            if (!empty($value)) {
                Log::info("{$key}: {$value}");
            }
        }

        return $cleanedVariants;
    }

    /**
     * Поиск постов.
     */
    public function searchPosts(array $queries)
    {
        // Проверяем, что массив запросов не пуст
        if (empty($queries)) {
            Log::error('No search queries provided');

            return [];
        }
        $baseQuery = Post::query()->published()->withTrashed(false);

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
            $queries[0].'%',    // Начинается с для title
            $queries[0].'%',    // Начинается с (в контенте)
            $queries[0].'%',    // Начинается с для slug
            '%'.$queries[0].'%', // Содержит для title
            '%'.$queries[0].'%', // Содержит для content
        ];

        $whereConditions = [];
        foreach ($queries as $query) {
            $whereConditions[] = '(title LIKE ? OR content LIKE ? OR slug LIKE ?)';
            $bindParams[] = '%'.$query.'%'; // Для title
            $bindParams[] = '%'.$query.'%'; // Для content
            $bindParams[] = '%'.$query.'%'; // Для slug
        }

        return $baseQuery
            ->selectRaw("*, $relevanceCase")
            ->whereRaw(implode(' OR ', $whereConditions), $bindParams)
            ->whereNull('deleted_at')
            ->orderByRaw('relevance_score DESC')
            ->limit(5)
            ->get();
    }

    /**
     * Поиск тегов.
     */
    protected function searchTags(array $queries)
    {
        return $this->performSearch(Tag::query(), $queries, ['slug']);
    }

    /**
     * Поиск пользователей.
     */
    protected function searchUsers(array $queries)
    {
        return $this->performSearch(User::query(), $queries, ['username', 'description', 'email']);
    }

    /**
     * Общий метод для выполнения поиска.
     */
    protected function performSearch($queryBuilder, array $queries, array $fields)
    {
        if (empty($queries)) {
            return [];
        }

        $queryBuilder->where(function ($q) use ($queries, $fields) {
            $this->buildSearchConditions($q, $queries, $fields);
        });

        return $queryBuilder->limit(5)->get();
    }

    /**
     * Построение условий поиска.
     */
    protected function buildSearchConditions($query, array $queries, array $fields)
    {
        foreach ($queries as $queryText) {
            foreach ($fields as $field) {
                $query->orWhere($field, 'LIKE', '%' . $queryText . '%');
            }
        }
    }
}
