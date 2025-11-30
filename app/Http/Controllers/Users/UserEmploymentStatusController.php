<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Http\Requests\EmploymentStatus\AssignEmploymentStatusRequest;
use App\Http\Requests\EmploymentStatus\RemoveEmploymentStatusRequest;
use App\Http\Resources\EmploymentStatusResource;
use App\Http\Resources\UserEmploymentStatusResource;
use App\Services\Users\UserEmploymentStatusService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Exception;
use OpenApi\Attributes as OA;

// Контроллер для работы с трудовым статусом пользователя
class UserEmploymentStatusController extends Controller
{
    protected UserEmploymentStatusService $userEmploymentStatusService;

    private const CACHE_KEY_USER_EMPLOYMENT_STATUS = 'user_employment_status_';

    public function __construct(UserEmploymentStatusService $userEmploymentStatusService)
    {
        $this->userEmploymentStatusService = $userEmploymentStatusService;
    }                        /**
     * @OA\Delete(
     *     path="/api/v1/user/employment-status/remove",
     *     tags={"Users"},
     *     summary="RemoveEmploymentStatus user employment status",
     *     description="RemoveEmploymentStatus user employment status",
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
public function removeEmploymentStatus(RemoveEmploymentStatusRequest $request)
    {
        try {
            $user = Auth::user();

            $user = $this->userEmploymentStatusService->removeEmploymentStatusFromUser($user->id);

            if ($user) {
                $this->forgetCache(self::CACHE_KEY_USER_EMPLOYMENT_STATUS . $user->id);

                Log::info('Employment status removed successfully', ['user_id' => $user->id]);

                return $this->successResponse('Employment status removed successfully');
            }

            Log::warning('User not found', ['user_id' => Auth::id()]);

            return $this->errorResponse('User not found', 404);
        } catch (Exception $e) {
            Log::error('Error removing employment status: ' . $e->getMessage(), ['user_id' => Auth::id()]);
            return $this->errorResponse('An error occurred while removing employment status: ' . $e->getMessage(), 500);
        }
    }
}
