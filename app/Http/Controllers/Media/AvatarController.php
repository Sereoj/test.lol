<?php

namespace App\Http\Controllers\Media;

use App\Http\Controllers\Controller;
use App\Http\Requests\Avatar\UploadAvatarRequest;
use App\Http\Resources\AvatarResource;
use App\Services\Media\AvatarService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use OpenApi\Attributes as OA;

// Контроллер для работы с аватарами пользователей
class AvatarController extends Controller
{
    protected AvatarService $avatarService;

    private const CACHE_MINUTES = 10;
    private const CACHE_KEY_USER_AVATARS = 'user_avatars_';

    public function __construct(AvatarService $avatarService)
    {
        $this->avatarService = $avatarService;
    }

                    /**
     * @OA\Post(
     *     path="/api/v1/avatar/{avatarId}/set-active",
     *     tags={"Avatars"},
     *     summary="SetActive avatar",
     *     description="SetActive avatar",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="avatarId",
     *         in="path",
     *         required=true,
     *         description="AvatarId",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Resource created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object"),
     *             @OA\Property(property="message", type="string", example="Resource created successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Validation failed"),
     *             @OA\Property(property="errors", type="object")
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
public function setActive($avatarId)
    {
        try {
            $user = Auth::user();
            $this->avatarService->setActive($user, $avatarId);
            Log::info('Avatar deleted successfully', ['user_id' => $user->id, 'avatar_id' => $avatarId]);

            return $this->successResponse(['message' => 'Avatar deleted successfully']);
        }catch (Exception $e) {

        }
    }
}
