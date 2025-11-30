<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\AssignStatusRequest;
use App\Http\Resources\StatusResource;
use App\Http\Resources\UserStatusResource;
use App\Services\Users\UserStatusService;
use Illuminate\Support\Facades\Auth;
use OpenApi\Attributes as OA;

// Контроллер для работы со статусами пользователей
class UserStatusController extends Controller
{
    protected UserStatusService $userStatusService;

    public function __construct(UserStatusService $userStatusService)
    {
        $this->userStatusService = $userStatusService;
    }                        /**
     * @OA\Delete(
     *     path="/api/v1/user/statuses/detach",
     *     tags={"Users"},
     *     summary="Detach user status",
     *     description="Detach user status",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Resource deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Resource deleted successfully")
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
public function detach()
    {
        try {
            $user = Auth::user();
            $this->userStatusService->detachStatus($user);
            return $this->successResponse(['message' => 'Status detached successfully']);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to detach status: ' . $e->getMessage(), 500);
        }
    }
}
