<?php

namespace App\Services;

use App\Models\Post;
use App\Utils\TextUtil;
use Illuminate\Support\Facades\Log;

class PostSearchService
{
    public function search(string $query)
    {
        $queries = $this->prepareSearchQueries($query);

        return [
            'posts' => $this->searchPosts($queries),
        ];
    }

    public function prepareSearchQueries(string $query): array
    {
        $variants = TextUtil::generateVariants(str($query)->lower());

        // Убираем дубли и фильтруем пустые строки
        $cleanedVariants = array_unique(array_filter($variants));

        foreach ($variants as $key => $value) {
            if (! empty($value)) {
                Log::info("{$key}: {$value}");
            }
        }

        return $cleanedVariants;
    }

    public function searchPosts(array $queries)
    {
        // Проверяем, что массив запросов не пуст
        if (empty($queries)) {
            Log::error('No search queries provided');

            return [];
        }
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
}
