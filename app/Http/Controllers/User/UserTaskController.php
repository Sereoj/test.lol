<?php

namespace App\Http\Controllers\User;

use App\Events\TaskCompleted;
use App\Helpers\UserTaskHelper;
use App\Http\Controllers\Controller;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserTaskController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $tasks = $user->tasks()->withPivot('progress', 'completed', 'period_start', 'period_end')->get();

        return response()->json($tasks);
    }

    public function completedTasks()
    {
        $user = Auth::user();
        $completedTasks = $user->tasks()->wherePivot('completed', true)->get();

        return response()->json($completedTasks);
    }

    public function inProgressTasks()
    {
        $user = Auth::user();
        $inProgressTasks = $user->tasks()->wherePivot('completed', false)->wherePivot('progress', '>', 0)->get();

        return response()->json($inProgressTasks);
    }

    public function notStartedTasks()
    {
        $user = Auth::user();
        $notStartedTasks = $user->tasks()->wherePivot('progress', 0)->get();

        return response()->json($notStartedTasks);
    }

    public function updateTaskProgress(Request $request, $taskId)
    {
        $user = Auth::user();

        // Валидация входящих данных
        $request->validate([
            'progress' => 'required|integer',
        ]);

        $task = Task::find($taskId);

        if (! $task) {
            return response()->json(['message' => 'Task not found'], 404);
        }

        $progressIncrement = $request->progress;

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

        if ($progressIncrement > $currentProgress) {
            $newProgress = $currentProgress + $progressIncrement;
            $completed = ($newProgress >= $task->target);

            $user->tasks()->syncWithoutDetaching([$task->id => [
                'progress' => $newProgress,
                'completed' => $completed,
                'period_start' => $periodStart ?? now(),
                'period_end' => UserTaskHelper::calculatePeriodEnd($task, $periodStart ?? now()),
            ]]);

            if ($completed) {
                event(new TaskCompleted($user, $task));
            }

            return response()->json(['message' => 'Task progress updated successfully']);
        } else {
            return response()->json(['message' => 'Invalid progress value'], 400);
        }
    }
}
