<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Services\UserTaskService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class UserTaskController extends Controller
{
    protected UserTaskService $userTaskService;

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
        $cacheKey = 'user_tasks_'.$user->id.'_'.md5($request->fullUrl());

        $tasks = Cache::remember($cacheKey, now()->addMinutes(10), function () use ($user, $request) {
            $filters = [
                'period' => $request->input('period'),
                'type' => $request->input('type'),
                'experience_reward' => $request->input('experience_reward'),
                'virtual_balance_reward' => $request->input('virtual_balance_reward'),
            ];

            return $this->userTaskService->getUserTasks($user, $filters);
        });

        return response()->json($tasks);
    }

    /**
     * Получить выполненные задачи пользователя.
     */
    public function completedTasks()
    {
        $user = Auth::user();
        $cacheKey = 'user_completed_tasks_'.$user->id;

        // Проверяем кеш
        $completedTasks = Cache::remember($cacheKey, now()->addMinutes(10), function () use ($user) {
            return $this->userTaskService->getCompletedTasks($user);
        });

        return response()->json($completedTasks);
    }

    /**
     * Получить задачи в процессе выполнения.
     */
    public function inProgressTasks()
    {
        $user = Auth::user();
        $cacheKey = 'user_in_progress_tasks_'.$user->id;

        // Проверяем кеш
        $inProgressTasks = Cache::remember($cacheKey, now()->addMinutes(10), function () use ($user) {
            return $this->userTaskService->getInProgressTasks($user);
        });

        return response()->json($inProgressTasks);
    }

    /**
     * Получить не начатые задачи.
     */
    public function notStartedTasks()
    {
        $user = Auth::user();
        $cacheKey = 'user_not_started_tasks_'.$user->id;

        // Проверяем кеш
        $notStartedTasks = Cache::remember($cacheKey, now()->addMinutes(10), function () use ($user) {
            return $this->userTaskService->getNotStartedTasks($user);
        });

        return response()->json($notStartedTasks);
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
            Cache::forget('user_tasks_'.$user->id);
            Cache::forget('user_in_progress_tasks_'.$user->id);

            return response()->json(['message' => 'Task progress updated successfully']);
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }
}
