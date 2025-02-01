<?php

namespace App\Http\Controllers;

use App\Http\Requests\SearchRequest;
use App\Services\PostSearchService;
use App\Services\SearchSuggestionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Log;

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
    public function search(SearchRequest $request)
    {
        $query = $request->input('query');

        $cacheKey = 'search_results_' . md5($query);

        $results = Cache::get($cacheKey);

        if ($results) {

            Log::info("Cache hit for query: {$query}", ['cache_key' => $cacheKey, 'results' => $results]);
        } else {
            Log::info("Cache miss for query: {$query}", ['cache_key' => $cacheKey]);

            $results = Cache::remember($cacheKey, now()->addMinutes(10), function() use ($query, $cacheKey) {
                $searchResults = $this->searchService->search($query);
                Log::info("Caching results for query: {$query}", ['cache_key' => $cacheKey, 'results' => $searchResults]);

                return $searchResults;
            });
        }

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
