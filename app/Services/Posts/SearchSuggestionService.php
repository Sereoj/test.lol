<?php

namespace App\Services\Posts;

use App\Models\Categories\Category;
use App\Models\Content\Tag;
use App\Models\Posts\Post;
use App\Models\Users\User;
use App\Services\API\LibreTranslateService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class SearchSuggestionService
{
    public function suggest($query, $limit = 10)
    {
        if (empty($query)) {
            return collect();
        }

        // Готовим запросы для поиска
        $queries = (new PostSearchService())->prepareSearchQueries($query);

        // Проверяем, есть ли результат в кэше
        $cacheKey = 'search_suggestions_'.md5($query);
        $suggestions = Cache::get($cacheKey);

        if (! $suggestions) {
            $suggestions = collect();

            $translatedQuery = $this->translateQuery($query);
            \Log::info('Тест: '.$translatedQuery);
            $queries[] = $translatedQuery;

            foreach ($queries as $preparedQuery) {
                // Посты
                // ПРИМЕНЕНИЕ selectRaw: используем сырой SQL для добавления вычисляемых полей
                // Это чище, чем смешивание select() с DB::raw()
                $postSuggestions = Post::query()
                    ->published()
                    ->where('title', 'like', "%{$preparedQuery}%")
                    ->selectRaw("title as text, 'post' as type, 100 as relevance_score")
                    ->take($limit)
                    ->get();

                // Пользователи
                $userSuggestions = User::query()
                    ->where('username', 'like', "%{$preparedQuery}%")
                    ->selectRaw("username as text, 'user' as type, 75 as relevance_score")
                    ->take($limit)
                    ->get();

                // Теги
                $tagSuggestions = Tag::query()
                    ->where('name', 'like', "%{$preparedQuery}%")
                    ->selectRaw("name as text, 'tag' as type, 50 as relevance_score")
                    ->take($limit)
                    ->get();

                // Категории
                $categorySuggestions = Category::query()
                    ->where('name', 'like', "%{$preparedQuery}%")
                    ->selectRaw("name as text, 'category' as type, 25 as relevance_score")
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
            Cache::put($cacheKey, $suggestions, now()->addMinutes(5));
        }

        return $suggestions;
    }

    /**
     * Метод для перевода текста
     *
     * @param  string  $query
     * @return string
     */
    protected function translateQuery($query)
    {
        // Попытаться получить переведённый текст из кэша
        $cacheKey = 'translation_'.md5($query);
        $translatedQuery = Cache::get($cacheKey);

        if (! $translatedQuery) {
            $translatedQuery = LibreTranslateService::translate($query, 'ru', 'en');

            if ($translatedQuery) {
                Cache::put($cacheKey, $translatedQuery, 60);
            } else {
                \Log::warning("Перевод не удался для запроса: {$query}");

                return $query;
            }
        }

        return $translatedQuery;
    }
}
