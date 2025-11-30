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
    }    /**
     * @OA\Get(
     *     path="/api/v1/user/statuses",
     *     tags={"Users"},
     *     summary="Get all user statuses",
     *     description="Get all user statuses",
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
     *                 @OA\Items(ref="#/components/schemas/UserStatus")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=500, description="Server error")
     * )
     */


    public function index()
    {
        try {
            $statuses = new UserStatusResource($this->userStatusService->getAll());
            return $this->successResponse($statuses);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve statuses: ' . $e->getMessage(), 500);
        }
    }    /**
     * @OA\Post(
     *     path="/api/v1/user/statuses/assign",
     *     tags={"Users"},
     *     summary="Assign user status",
     *     description="Assign user status",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/AssignStatusRequest")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Resource created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/UserStatus")
     *         )
     *     ),
     *     @OA\Response(response=500, description="Server error")
     * )
     */


    public function assign(AssignStatusRequest $request)
    {
        try {
            $user = Auth::user();
            $status = $this->userStatusService->assignStatus($user, $request->input('status_id'));
            return $this->successResponse(
                [
                    'message' => 'Status assigned successfully',
                    'status' => new StatusResource($status)
                ]);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to assign status: ' . $e->getMessage(), 500);
        }
    }    /**
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
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="message", type="string", example="Resource deleted successfully")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=500, description="Server error")
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
