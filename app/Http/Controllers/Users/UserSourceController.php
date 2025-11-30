<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Http\Requests\Source\AddSourceRequest;
use App\Http\Requests\Source\RemoveSourceRequest;
use App\Services\Users\UserSourceService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Exception;
use OpenApi\Attributes as OA;

// Контроллер для работы с источниками пользователей
class UserSourceController extends Controller
{
    protected UserSourceService $userSourceService;
    
    private const CACHE_MINUTES = 10;
    private const CACHE_KEY_USER_SOURCES = 'user_sources_';

    public function __construct(UserSourceService $userSourceService)
    {
        $this->userSourceService = $userSourceService;
    }

        /**
     * @OA\Get(
     *     path="/api/v1/user/sources",
     *     tags={"Users"},
     *     summary="GetUserSources user source",
     *     description="GetUserSources user source",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/UserSource")
     *         )
     *     ),
     *     @OA\Response(response=500, description="Server error")
     * )
     */
public function getUserSources()
    {
        try {
            $user = Auth::user();
            $cacheKey = self::CACHE_KEY_USER_SOURCES . $user->id;

            $sources = $this->getFromCacheOrStore($cacheKey, self::CACHE_MINUTES, function () use ($user) {
                return $this->userSourceService->getUserSources($user->id);
            });
            
            Log::info('User sources retrieved successfully', ['user_id' => $user->id]);

            return $this->successResponse($sources);
        } catch (Exception $e) {
            Log::error('An error occurred while retrieving sources: ' . $e->getMessage(), ['user_id' => Auth::id()]);
            return $this->errorResponse('An error occurred while retrieving sources: ' . $e->getMessage(), 500);
        }
    }
}
