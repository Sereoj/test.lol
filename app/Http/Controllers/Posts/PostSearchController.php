<?php

namespace App\Http\Controllers\Posts;

use App\Http\Controllers\Controller;
use App\Http\Requests\SearchRequest;
use App\Http\Resources\Search\SearchThumbMediaResource;
use App\Http\Resources\Search\ShortSearchTagResource;
use App\Http\Resources\ThumbUserMediaResource;
use App\Http\Resources\UserSearchResource;
use App\Services\Posts\PostSearchService;
use App\Services\Posts\SearchSuggestionService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use OpenApi\Attributes as OA;

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
     * @OA\Get(
     *     path="/api/v1/search",
     *     tags={"Posts"},
     *     summary="Search post search",
     *     description="Search post search",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Items per page",
     *         @OA\Schema(type="integer", example=15)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/PostSearch")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=500, description="Server error")
     * )
     */
public function search(SearchRequest $request)
    {
        try {
            $query = $request->input('query');
            $cacheKey = self::CACHE_KEY_SEARCH_RESULTS .'_all_'. md5($query);

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

    // Поиск постов по тегам   
    /**
     * @OA\Get(
     *     path="/api/v1/search/tags",
     *     tags={"Posts"},
     *     summary="SearchTags post search",
     *     description="SearchTags post search",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/PostSearch")
     *         )
     *     ),
     *     @OA\Response(response=500, description="Server error")
     * )
     */

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
    /**
     * @OA\Get(
     *     path="/api/v1/search/posts",
     *     tags={"Posts"},
     *     summary="SearchPosts post search",
     *     description="SearchPosts post search",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/PostSearch")
     *         )
     *     ),
     *     @OA\Response(response=500, description="Server error")
     * )
     */

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
    /**
     * @OA\Get(
     *     path="/api/v1/search/users",
     *     tags={"Posts"},
     *     summary="SearchUsers post search",
     *     description="SearchUsers post search",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/PostSearch")
     *         )
     *     ),
     *     @OA\Response(response=500, description="Server error")
     * )
     */

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
    /**
     * @OA\Get(
     *     path="/api/v1/search/suggest",
     *     tags={"Posts"},
     *     summary="Suggest post search",
     *     description="Suggest post search",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/PostSearch")
     *         )
     *     ),
     *     @OA\Response(response=500, description="Server error")
     * )
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
