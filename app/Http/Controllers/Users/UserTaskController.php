<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Services\Users\UserTaskService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use OpenApi\Attributes as OA;

// Контроллер для работы с задачами пользователей
class UserTaskController extends Controller
{
    protected UserTaskService $userTaskService;
    
    private const CACHE_MINUTES = 10;
    private const CACHE_KEY_USER_TASKS = 'user_tasks_';
    private const CACHE_KEY_COMPLETED_TASKS = 'user_completed_tasks_';
    private const CACHE_KEY_IN_PROGRESS_TASKS = 'user_in_progress_tasks_';
    private const CACHE_KEY_NOT_STARTED_TASKS = 'user_not_started_tasks_';

    public function __construct(UserTaskService $userTaskService)
    {
        $this->userTaskService = $userTaskService;
    }

                        /**
     * @OA\Post(
     *     path="/api/v1/user/tasks/{task}/progress",
     *     tags={"Users"},
     *     summary="UpdateTaskProgress user task",
     *     description="UpdateTaskProgress user task",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="task",
     *         in="path",
     *         required=true,
     *         description="Task",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/Request")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Resource created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/UserTask")
     *         )
     *     ),
     *     @OA\Response(response=500, description="Server error")
     * )
     */
public function updateTaskProgress(Request $request, $taskId)
    {
        $user = Auth::user();

        $request->validate([
            'progress' => 'required|integer|min:0',
        ]);

        try {
            $this->userTaskService->updateTaskProgress($user, $taskId, $request->progress);
            $this->forgetCache([
                self::CACHE_KEY_USER_TASKS . $user->id,
                self::CACHE_KEY_IN_PROGRESS_TASKS . $user->id,
                self::CACHE_KEY_COMPLETED_TASKS . $user->id,
                self::CACHE_KEY_NOT_STARTED_TASKS . $user->id
            ]);
            
            return $this->successResponse(['message' => 'Task progress updated successfully']);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 400);
        }
    }
}
