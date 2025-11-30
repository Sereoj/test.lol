<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserPersonalizationRequest;
use App\Services\Users\UserPersonalizationService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use OpenApi\Attributes as OA;

// Контроллер для работы с персонализацией пользователя
class UserPersonalizationController extends Controller
{
    protected UserPersonalizationService $userPersonalizationService;
    public function __construct(UserPersonalizationService $userPersonalizationService)
    {
        $this->userPersonalizationService = $userPersonalizationService;
    }

    // Обновление персонализации пользователя   
    /**
     * @OA\Post(
     *     path="/api/v1/user/personalization",
     *     tags={"Users"},
     *     summary="Update user personalization",
     *     description="Update user personalization",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/UserPersonalizationRequest")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Resource created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/UserPersonalization")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Resource not found"),
     *     @OA\Response(response=500, description="Server error")
     * )
     */

    public function update(UserPersonalizationRequest $request)
    {
        try {
            $user = Auth::user();
            $userPersonalization = $this->userPersonalizationService->update($user, $request->validated());
            return $this->successResponse($userPersonalization);
        }catch (\Exception $exception){
            Log::error($exception);
            return $this->errorResponse($exception->getMessage());
        }
    }
}
