<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLevelRequest;
use App\Services\Users\UserLevelService;
use Exception;
use Illuminate\Support\Facades\Log;
use OpenApi\Attributes as OA;

// Контроллер для работы с уровнями пользователей
class UserLevelController extends Controller
{
    protected UserLevelService $levelService;

    private const CACHE_MINUTES = 60;
    private const CACHE_KEY_LEVELS_LIST = 'levels_list';

    public function __construct(UserLevelService $levelService)
    {
        $this->levelService = $levelService;
    }

            /**
     * @OA\Post(
     *     path="/api/v1/levels",
     *     tags={"Users"},
     *     summary="Create new user level",
     *     description="Create new user level",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/StoreLevelRequest")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Resource created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/UserLevel")
     *         )
     *     ),
     *     @OA\Response(response=500, description="Server error")
     * )
     */
public function store(StoreLevelRequest $request)
    {
        try {
            $level = $this->levelService->createLevel($request->name, $request->experience_required);

            Log::info('Level created successfully', ['level_id' => $level->id]);

            $this->forgetCache(self::CACHE_KEY_LEVELS_LIST);

            return $this->successResponse($level, 201);
        } catch (Exception $e) {
            Log::error('Error creating level: ' . $e->getMessage(), ['data' => $request->all()]);
            return $this->errorResponse($e->getMessage(), 500);
        }
    }
}
