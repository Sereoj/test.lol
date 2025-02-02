<?php

namespace App\Services;

use App\Events\TaskCompleted;
use App\Models\Task;
use App\Models\User;
use App\Models\UserTask;
use Exception;
use Illuminate\Support\Facades\Log;

class UserTaskService
{
    public function processTasks(User $user, string $taskType)
    {
        $tasks = UserTask::with('task')
            ->where('user_id', $user->id)
            ->whereHas('task', function ($query) use ($taskType) {
                $query->where('type', $taskType);
            })
            ->get();

        Log::info("Tasks: $tasks");

        foreach ($tasks as $task) {
            if ($task->completed) {
                Log::info("Task already completed: $task");

                continue;
            }

            $task->increment('progress', 1);
            Log::info("Task progress incremented: $task");

            if (! is_null($task->task->target) && $task->progress >= $task->task->target) {
                $task->update([
                    'completed' => true,
                    'progress' => $task->task->target,
                ]);
                $user->increment('experience', $task->task->experience_reward);
                //$user->increment('virtual_balance', $task->task->virtual_balance_reward);
                $user->save();

                Log::info("Added {$task->task->experience_reward} experience and {$task->task->virtual_balance_reward} virtual balance to user.");
            }
            $task->save();
            Log::info("Task progress: {$task->progress} / Target: {$task->task->target}");
        }
    }

    /**
     * Получить все задачи пользователя.
     */
    public function getUserTasks(User $user, $filters)
    {
        $query = $user->tasks()->withPivot('progress', 'completed', 'period_start', 'period_end');

        // Фильтрация по period
        if (isset($filters['period'])) {
            $query->wherePivot('period_start', '<=', now())
                ->wherePivot('period_end', '>=', now());
        }

        // Фильтрация по type
        if (isset($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        // Фильтрация по experience_reward
        if (isset($filters['experience_reward'])) {
            $query->where('experience_reward', '>=', $filters['experience_reward']);
        }

        // Фильтрация по virtual_balance_reward
        if (isset($filters['virtual_balance_reward'])) {
            $query->where('virtual_balance_reward', '>=', $filters['virtual_balance_reward']);
        }

        return $query->get();
    }

    /**
     * Получить выполненные задачи пользователя.
     */
    public function getCompletedTasks(User $user)
    {
        return $user->tasks()->wherePivot('completed', true)->get();
    }

    /**
     * Получить задачи в процессе выполнения.
     */
    public function getInProgressTasks(User $user)
    {
        return $user->tasks()->wherePivot('completed', false)->wherePivot('progress', '>', 0)->get();
    }

    /**
     * Получить не начатые задачи.
     */
    public function getNotStartedTasks(User $user)
    {
        return $user->tasks()->wherePivot('progress', 0)->get();
    }

    /**
     * Обновить прогресс задачи.
     */
    public function updateTaskProgress(User $user, $taskId, $progressIncrement)
    {
        $task = Task::find($taskId);

        if (! $task) {
            throw new Exception('Task not found');
        }

        // Проверяем, существует ли задача для пользователя
        $currentTask = $user->tasks()->where('task_id', $task->id)->first();

        if (! $currentTask) {
            throw new Exception('Task not found for the user');
        }

        $currentProgress = $currentTask->pivot->progress;
        $periodStart = $currentTask->pivot->period_start;
        $periodEnd = $currentTask->pivot->period_end;

        // Проверка периода выполнения задания
        if ($this->isPeriodExpired($periodEnd)) {
            throw new Exception('Task period has expired');
        }

        // Проверка, что новый прогресс не меньше текущего
        if ($progressIncrement > $currentProgress) {
            $newProgress = $currentProgress + $progressIncrement;
            $completed = ($newProgress >= $task->target);

            // Синхронизация задачи
            $user->tasks()->syncWithoutDetaching([$task->id => [
                'progress' => $newProgress,
                'completed' => $completed,
                'period_start' => $periodStart ?? now(),
                'period_end' => $this->calculatePeriodEnd($task, $periodStart ?? now()),
            ]]);

            if ($completed) {
                event(new TaskCompleted($user, $task));
            }

            return true;
        }

        throw new Exception('Invalid progress value');
    }

    /**
     * Проверка, истек ли период выполнения задания.
     */
    private function isPeriodExpired($periodEnd)
    {
        return now() > $periodEnd;
    }

    /**
     * Рассчитать конечную дату периода.
     */
    private function calculatePeriodEnd($task, $periodStart)
    {
        switch ($task->period) {
            case 'month':
                return $periodStart->addMonth();
            case 'year':
                return $periodStart->addYear();
            case 'week':
            default:
                return $periodStart->addWeek();
        }
    }
}
