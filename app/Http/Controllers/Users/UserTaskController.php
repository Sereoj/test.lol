<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Services\Users\UserTaskService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
     * Получить все задачи пользователя.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $cacheKey = self::CACHE_KEY_USER_TASKS . $user->id . '_' . md5($request->fullUrl());

        $tasks = $this->getFromCacheOrStore($cacheKey, self::CACHE_MINUTES, function () use ($user, $request) {
            $filters = [
                'period' => $request->input('period'),
                'type' => $request->input('type'),
                'experience_reward' => $request->input('experience_reward'),
                'virtual_balance_reward' => $request->input('virtual_balance_reward'),
            ];

            return $this->userTaskService->getUserTasks($user, $filters);
        });

        return $this->successResponse($tasks);
    }

    /**
     * Получить выполненные задачи пользователя.
     */
    public function completedTasks()
    {
        $user = Auth::user();
        $cacheKey = self::CACHE_KEY_COMPLETED_TASKS . $user->id;

        $completedTasks = $this->getFromCacheOrStore($cacheKey, self::CACHE_MINUTES, function () use ($user) {
            return $this->userTaskService->getCompletedTasks($user);
        });

        return $this->successResponse($completedTasks);
    }

    /**
     * Получить задачи в процессе выполнения.
     */
    public function inProgressTasks()
    {
        $user = Auth::user();
        $cacheKey = self::CACHE_KEY_IN_PROGRESS_TASKS . $user->id;

        $inProgressTasks = $this->getFromCacheOrStore($cacheKey, self::CACHE_MINUTES, function () use ($user) {
            return $this->userTaskService->getInProgressTasks($user);
        });

        return $this->successResponse($inProgressTasks);
    }

    /**
     * Получить не начатые задачи.
     */
    public function notStartedTasks()
    {
        $user = Auth::user();
        $cacheKey = self::CACHE_KEY_NOT_STARTED_TASKS . $user->id;

        $notStartedTasks = $this->getFromCacheOrStore($cacheKey, self::CACHE_MINUTES, function () use ($user) {
            return $this->userTaskService->getNotStartedTasks($user);
        });

        return $this->successResponse($notStartedTasks);
    }

    /**
     * Обновить прогресс задачи.
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
