<?php

namespace App\Http\Controllers\Posts;

use App\Http\Controllers\Controller;
use App\Http\Requests\SearchRequest;
use App\Services\Posts\PostSearchService;
use App\Services\Posts\SearchSuggestionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Exception;

class PostSearchController extends Controller
{
    protected PostSearchService $searchService;
    private SearchSuggestionService $searchSuggestionService;
    
    private const CACHE_MINUTES = 10;
    private const CACHE_KEY_SEARCH_RESULTS = 'search_results_';
    private const CACHE_KEY_SEARCH_SUGGESTIONS = 'search_suggestions_';

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
        try {
            $query = $request->input('query');
            $cacheKey = self::CACHE_KEY_SEARCH_RESULTS . md5($query);
            
            $results = $this->getFromCacheOrStore($cacheKey, self::CACHE_MINUTES, function () use ($query, $cacheKey) {
                $searchResults = $this->searchService->search($query);
                Log::info("Caching results for query: {$query}", ['cache_key' => $cacheKey, 'results' => $searchResults]);
                return $searchResults;
            });
            
            Log::info("Results returned for query: {$query}");
            
            return $this->successResponse($results);
        } catch (Exception $e) {
            Log::error('Error searching posts: ' . $e->getMessage(), ['query' => $request->input('query')]);
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Предложение популярных запросов.
     */
    public function suggest(Request $request)
    {
        try {
            $query = $request->input('query');
            $cacheKey = self::CACHE_KEY_SEARCH_SUGGESTIONS . md5($query);
            
            $suggestions = $this->getFromCacheOrStore($cacheKey, self::CACHE_MINUTES, function () use ($query) {
                return $this->searchSuggestionService->suggest($query);
            });
            
            Log::info("Suggestions returned for query: {$query}");
            
            return $this->successResponse($suggestions);
        } catch (Exception $e) {
            Log::error('Error generating search suggestions: ' . $e->getMessage(), ['query' => $request->input('query')]);
            return $this->errorResponse($e->getMessage(), 500);
        }
    }
}
