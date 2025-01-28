<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class SearchSuggestionService
{
    public function suggest($query, $limit = 10)
    {
        // Готовим запросы для поиска
        $queries = (new PostSearchService)->prepareSearchQueries($query);

        // Проверяем, есть ли результат в кэше
        $cacheKey = 'search_suggestions_'.md5($query);
        $suggestions = Cache::get($cacheKey);

        if (! $suggestions) {
            $suggestions = collect();

            foreach ($queries as $preparedQuery) {
                // Посты
                $postSuggestions = Post::query()
                    ->where('title', 'like', "%{$preparedQuery}%")
                    ->select('title as text', DB::raw("'post' as type"), DB::raw('100 as relevance_score'))
                    ->take($limit)
                    ->get();

                // Пользователи
                $userSuggestions = User::query()
                    ->where('username', 'like', "%{$preparedQuery}%")
                    ->select('username as text', DB::raw("'user' as type"), DB::raw('75 as relevance_score'))
                    ->take($limit)
                    ->get();

                // Теги
                $tagSuggestions = Tag::query()
                    ->where('name', 'like', "%{$preparedQuery}%")
                    ->select('name as text', DB::raw("'tag' as type"), DB::raw('50 as relevance_score'))
                    ->take($limit)
                    ->get();

                // Категории
                $categorySuggestions = Category::query()
                    ->where('name', 'like', "%{$preparedQuery}%")
                    ->select('name as text', DB::raw("'category' as type"), DB::raw('25 as relevance_score'))
                    ->take($limit)
                    ->get();

                // Объединяем все предложения
                $suggestions = $suggestions->merge($postSuggestions)
                    ->merge($userSuggestions)
                    ->merge($tagSuggestions)
                    ->merge($categorySuggestions);
            }

            // Сортируем по релевантности и ограничиваем количество результатов
            $suggestions = $suggestions->unique('text')
                ->sortByDesc('relevance')
                ->take($limit)
                ->values();

            // Сохраняем в кэш
            Cache::put($cacheKey, $suggestions, 60); // 60 минут
        }

        return $suggestions;
    }
}
