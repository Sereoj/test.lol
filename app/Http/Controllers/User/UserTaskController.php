<?php

namespace App\Http\Controllers\User;

use App\Events\TaskCompleted;
use App\Helpers\UserTaskHelper;
use App\Http\Controllers\Controller;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class UserTaskController extends Controller
{
    /**
     * Получить все задачи пользователя.
     */
    public function index()
    {
        $user = Auth::user();
        $cacheKey = 'user_tasks_' . $user->id;  // Уникальный ключ для кеша

        // Проверяем, есть ли данные в кеше
        $tasks = Cache::remember($cacheKey, now()->addMinutes(10), function () use ($user) {
            return $user->tasks()->withPivot('progress', 'completed', 'period_start', 'period_end')->get();
        });

        return response()->json($tasks);
    }

    /**
     * Получить выполненные задачи пользователя.
     */
    public function completedTasks()
    {
        $user = Auth::user();
        $cacheKey = 'user_completed_tasks_' . $user->id;

        // Проверяем кеш
        $completedTasks = Cache::remember($cacheKey, now()->addMinutes(10), function () use ($user) {
            return $user->tasks()->wherePivot('completed', true)->get();
        });

        return response()->json($completedTasks);
    }

    /**
     * Получить задачи в процессе выполнения.
     */
    public function inProgressTasks()
    {
        $user = Auth::user();
        $cacheKey = 'user_in_progress_tasks_' . $user->id;

        // Проверяем кеш
        $inProgressTasks = Cache::remember($cacheKey, now()->addMinutes(10), function () use ($user) {
            return $user->tasks()->wherePivot('completed', false)->wherePivot('progress', '>', 0)->get();
        });

        return response()->json($inProgressTasks);
    }

    /**
     * Получить не начатые задачи.
     */
    public function notStartedTasks()
    {
        $user = Auth::user();
        $cacheKey = 'user_not_started_tasks_' . $user->id;

        // Проверяем кеш
        $notStartedTasks = Cache::remember($cacheKey, now()->addMinutes(10), function () use ($user) {
            return $user->tasks()->wherePivot('progress', 0)->get();
        });

        return response()->json($notStartedTasks);
    }

    /**
     * Обновить прогресс задачи.
     */
    public function updateTaskProgress(Request $request, $taskId)
    {
        $user = Auth::user();

        // Валидация входящих данных
        $request->validate([
            'progress' => 'required|integer|min:0',
        ]);

        $task = Task::find($taskId);

        if (! $task) {
            return response()->json(['message' => 'Task not found'], 404);
        }

        // Проверяем, существует ли задача для пользователя
        $currentTask = $user->tasks()->where('task_id', $task->id)->first();

        if (! $currentTask) {
            return response()->json(['message' => 'Task not found for the user', 'user_id' => $user->id, 'task_id' => $task->id], 404);
        }

        $currentProgress = $currentTask->pivot->progress;
        $periodStart = $currentTask->pivot->period_start;
        $periodEnd = $currentTask->pivot->period_end;

        // Проверка периода выполнения задания
        if (UserTaskHelper::isPeriodExpired($task, $periodStart, $periodEnd)) {
            return response()->json(['message' => 'Task period has expired'], 400);
        }

        // Инкремент прогресса
        $progressIncrement = $request->progress;

        if ($progressIncrement <= 0) {
            return response()->json(['message' => 'Progress increment must be a positive value'], 400);
        }

        // Проверка, что новый прогресс не меньше текущего
        if ($progressIncrement > $currentProgress) {
            $newProgress = $currentProgress + $progressIncrement;
            $completed = ($newProgress >= $task->target);

            // Синхронизация задачи и обновление кеша
            $user->tasks()->syncWithoutDetaching([$task->id => [
                'progress' => $newProgress,
                'completed' => $completed,
                'period_start' => $periodStart ?? now(),
                'period_end' => UserTaskHelper::calculatePeriodEnd($task, $periodStart ?? now()),
            ]]);

            // Кешируем обновленную задачу
            Cache::forget('user_tasks_' . $user->id); // Очищаем кеш задач
            Cache::forget('user_in_progress_tasks_' . $user->id); // Очищаем кеш задач в процессе

            // Отправка события, если задача завершена
            if ($completed) {
                event(new TaskCompleted($user, $task));
            }

            return response()->json(['message' => 'Task progress updated successfully']);
        }

        return response()->json(['message' => 'Invalid progress value'], 400);
    }
}
