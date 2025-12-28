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

    public function searchInTags(string $query)
    {
        // Подготовка вариантов поискового запроса
        $queries = $this->prepareSearchQueries($query);
        return $this->searchTags($queries);
    }

    public function searchInPosts(string $query)
    {
        // Подготовка вариантов поискового запроса
        $queries = $this->prepareSearchQueries($query);
        return $this->searchPosts($queries);
    }

    public function searchInUsers(string $query)
    {
        // Подготовка вариантов поискового запроса
        $queries = $this->prepareSearchQueries($query);
        return $this->searchUsers($queries);
    }

    /**
     * Подготовка вариантов поискового запроса.
     */
    public function prepareSearchQueries(?string $query): array
    {
        if (empty($query)) {
            return [];
        }

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

        // 1. Поиск постов по title, content, slug
        $baseQuery = Post::query()->with('media', 'user', 'statistics');

        // Строим CASE для relevance scoring с учетом ВСЕХ вариантов запроса
        $relevanceCases = [];
        $relevanceParams = [];

        foreach ($queries as $query) {
            // Точное совпадение (наивысший приоритет)
            $relevanceCases[] = 'WHEN title = ? THEN 100';
            $relevanceParams[] = $query;

            // Начинается с (высокий приоритет)
            $relevanceCases[] = 'WHEN title LIKE ? THEN 75';
            $relevanceParams[] = $query.'%';

            $relevanceCases[] = 'WHEN content LIKE ? THEN 75';
            $relevanceParams[] = $query.'%';

            // Slug (средний приоритет)
            $relevanceCases[] = 'WHEN slug LIKE ? THEN 50';
            $relevanceParams[] = $query.'%';

            // Содержит (низкий приоритет)
            $relevanceCases[] = 'WHEN title LIKE ? THEN 50';
            $relevanceParams[] = '%'.$query.'%';

            $relevanceCases[] = 'WHEN content LIKE ? THEN 50';
            $relevanceParams[] = '%'.$query.'%';
        }

        $relevanceCase = 'CASE ' . implode(' ', $relevanceCases) . ' ELSE 25 END as relevance_score';

        // Параметры для WHERE условий
        $whereConditions = [];
        $whereParams = [];
        foreach ($queries as $query) {
            $whereConditions[] = '(title LIKE ? OR content LIKE ? OR slug LIKE ?)';
            $whereParams[] = '%'.$query.'%'; // Для title
            $whereParams[] = '%'.$query.'%'; // Для content
            $whereParams[] = '%'.$query.'%'; // Для slug
        }

        // ВАЖНО: selectRaw и whereRaw используют РАЗНЫЕ массивы параметров
        $postsByContent = $baseQuery
            ->selectRaw("*, $relevanceCase", $relevanceParams)
            ->whereRaw(implode(' OR ', $whereConditions), $whereParams)
            ->orderByRaw('relevance_score DESC')
            ->limit(50)
            ->get();

        // 2. Поиск постов по тегам
        $tags = $this->searchTags($queries);
        $postsByTags = collect();

        if ($tags->count() > 0) {
            $tagIds = $tags->pluck('id')->toArray();

            $postsByTags = Post::query()
                ->with('media', 'user', 'statistics')
                ->whereHas('tags', function ($query) use ($tagIds) {
                    $query->whereIn('tags.id', $tagIds);
                })
                ->limit(50)
                ->get();
        }

        // 3. Объединяем результаты и убираем дубликаты
        $allPosts = $postsByContent->merge($postsByTags)->unique('id');

        return $allPosts->take(50);
    }

    /**
     * Поиск тегов.
     */
    protected function searchTags(array $queries): mixed
    {
        if (empty($queries)) {
            return collect();
        }

        $queryBuilder = Tag::withCount('posts');

        $queryBuilder->where(function ($q) use ($queries) {
            foreach ($queries as $query) {
                $q->orWhere('slug', 'LIKE', '%' . $query . '%')
                  ->orWhereRaw('LOWER(JSON_UNQUOTE(JSON_EXTRACT(name, "$.ru"))) LIKE ?', ['%' . strtolower($query) . '%'])
                  ->orWhereRaw('LOWER(JSON_UNQUOTE(JSON_EXTRACT(name, "$.en"))) LIKE ?', ['%' . strtolower($query) . '%']);
            }
        });

        return $queryBuilder->limit(30)->get();
    }

    /**
     * Поиск пользователей.
     */
    protected function searchUsers(array $queries)
    {
        return $this->performSearch(
            User::with(['avatars', 'badges', 'onlineStatus', 'role'])
                ->withCount('followers'),
            $queries,
            ['username', 'description', 'slug'],
            30
        );
    }

    /**
     * Общий метод для выполнения поиска.
     */
    protected function performSearch($queryBuilder, array $queries, array $fields, int $limit = 50)
    {
        if (empty($queries)) {
            return [];
        }

        $queryBuilder->where(function ($q) use ($queries, $fields) {
            $this->buildSearchConditions($q, $queries, $fields);
        });

        return $queryBuilder->limit($limit)->get();
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
