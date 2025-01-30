<?php

namespace App\Http\Controllers;

use App\Services\PostSearchService;
use App\Services\SearchSuggestionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class PostSearchController extends Controller
{
    protected PostSearchService $searchService;

    private SearchSuggestionService $searchSuggestionService;

    public function __construct(PostSearchService $searchService, SearchSuggestionService $searchSuggestionService)
    {
        $this->searchService = $searchService;
        $this->searchSuggestionService = $searchSuggestionService;
    }

    /**
     * Поиск постов по запросу.
     */
    public function search(Request $request)
    {
        $query = $request->input('query');

        // Валидация запроса
        $validated = $request->validate([
            'query' => 'required|string|min:3',
        ]);

        // Использование кеша для поисковых запросов
        $cacheKey = 'search_results_' . md5($query);

        $results = Cache::remember($cacheKey, now()->addMinutes(10), function() use ($query) {
            return $this->searchService->search($query);
        });

        return response()->json($results);
    }

    /**
     * Предложение популярных запросов.
     */
    public function suggest(Request $request)
    {
        $query = $request->input('query');

        // Использование кеша для предложений
        $cacheKey = 'search_suggestions_' . md5($query);

        $suggestions = Cache::remember($cacheKey, now()->addMinutes(10), function() use ($query) {
            return $this->searchSuggestionService->suggest($query);
        });

        return response()->json($suggestions);
    }
}
