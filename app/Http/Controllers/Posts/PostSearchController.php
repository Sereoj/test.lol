<?php

namespace App\Http\Controllers\Posts;

use App\Http\Controllers\Controller;
use App\Http\Requests\SearchRequest;
use App\Http\Resources\Search\ShortSearchTagResource;
use App\Http\Resources\ThumbUserMediaResource;
use App\Http\Resources\UserSearchResource;
use App\Services\Posts\PostSearchService;
use App\Services\Posts\SearchSuggestionService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

// Контроллер для поиска постов
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
            $userId = $request->user()?->id;
            $cacheKey = self::CACHE_KEY_SEARCH_RESULTS .'_all_'. md5($query . '_' . ($userId ?? 'guest'));

            $results = $this->getFromCacheOrStore($cacheKey, self::CACHE_MINUTES, function () use ($query, $cacheKey) {
                $searchResults = $this->searchService->search($query);

                // Преобразуем RAW данные в Resources
                $formattedResults = [
                    'posts' => ThumbUserMediaResource::collection($searchResults['posts']),
                    'tags' => ShortSearchTagResource::collection($searchResults['tags']),
                    'users' => UserSearchResource::collection($searchResults['users']),
                ];

                Log::info("Caching results for query: {$query}", ['cache_key' => $cacheKey]);
                return $formattedResults;
            });

            Log::info("Results returned for query: {$query}");

            return $this->successResponse($results);
        } catch (Exception $e) {
            Log::error('Error searching posts: ' . $e->getMessage(), ['query' => $request->input('query')]);
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    // Поиск постов по тегам
    public function searchTags(SearchRequest $request)
    {
        try {
            $query = $request->input('query');
            $cacheKey = self::CACHE_KEY_SEARCH_RESULTS .'_tags_'. md5($query);

            $results = $this->getFromCacheOrStore($cacheKey, self::CACHE_MINUTES, function () use ($query, $cacheKey) {
                $searchResults = ShortSearchTagResource::collection($this->searchService->searchInTags($query));
                Log::info("Caching results for query: {$query}", ['cache_key' => $cacheKey, 'results' => $searchResults]);
                return $searchResults;
            });

            Log::info("Results returned for query: {$query}");

            return $this->successResponse($results);
        }catch (Exception $e) {
            Log::error('Error searching posts: ' . $e->getMessage(), ['query' => $request->input('query')]);
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    // Поиск постов по запросу
    public function searchPosts(SearchRequest $request)
    {
        try {
            $query = $request->input('query');
            $cacheKey = self::CACHE_KEY_SEARCH_RESULTS .'_posts_'. md5($query);

            $results = $this->getFromCacheOrStore($cacheKey, self::CACHE_MINUTES, function () use ($query, $cacheKey) {
                $searchResults = ThumbUserMediaResource::collection($this->searchService->searchInPosts($query));
                Log::info("Caching results for query: {$query}", ['cache_key' => $cacheKey, 'results' => $searchResults]);
                return $searchResults;
            });

            Log::info("Results returned for query: {$query}");

            return $this->successResponse($results);
        }catch (Exception $e) {
            Log::error('Error searching posts: ' . $e->getMessage(), ['query' => $request->input('query')]);
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    // Поиск постов по пользователям
    public function searchUsers(SearchRequest $request)
    {
        try {
            $query = $request->input('query');
            $cacheKey = self::CACHE_KEY_SEARCH_RESULTS .'_users_'. md5($query);

            $results = $this->getFromCacheOrStore($cacheKey, self::CACHE_MINUTES, function () use ($query, $cacheKey) {
                $searchResults = $this->searchService->searchInUsers($query);
                Log::info("Caching results for query: {$query}", ['cache_key' => $cacheKey, 'results' => $searchResults]);
                return $searchResults;
            });

            Log::info("Results returned for query: {$query}");
                //return $this->successResponse($results);
            return $this->successResponse(UserSearchResource::collection($results));
        }catch (Exception $e) {
            Log::error('Error searching posts: ' . $e->getMessage(), ['query' => $request->input('query')]);
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    // Предложение популярных запросов
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
