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
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Internal server error")
     *         )
     *     )
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
