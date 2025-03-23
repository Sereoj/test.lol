<?php

namespace App\Services\Users;

use App\Events\TaskCompleted;
use App\Models\Content\Task;
use App\Models\Users\User;
use App\Models\Users\UserTask;
use App\Services\Base\SimpleService;
use Exception;
use Illuminate\Support\Facades\Log;

class UserTaskService extends SimpleService
{
    /**
     * Префикс кеша
     *
     * @var string
     */
    protected string $cachePrefix = 'user_task';

    /**
     * Время хранения кеша в минутах
     *
     * @var int
     */
    protected int $defaultCacheMinutes = 60;

    /**
     * Конструктор
     */
    public function __construct()
    {
        parent::__construct();
        $this->setLogPrefix('UserTaskService');
    }

    /**
     * Обработать задачи пользователя определенного типа
     *
     * @param User $user Пользователь
     * @param string $taskType Тип задачи
     * @return void
     */
    public function processTasks(User $user, string $taskType)
    {
        $this->logInfo("Обработка задач пользователя", [
            'user_id' => $user->id,
            'task_type' => $taskType
        ]);
        
        return $this->transaction(function () use ($user, $taskType) {
            $tasks = UserTask::with('task')
                ->where('user_id', $user->id)
                ->whereHas('task', function ($query) use ($taskType) {
                    $query->where('type', $taskType);
                })
                ->get();

            $this->logInfo("Найдено задач", ['count' => $tasks->count()]);

            foreach ($tasks as $task) {
                if ($task->completed) {
                    $this->logInfo("Задача уже выполнена", [
                        'task_id' => $task->task_id,
                        'user_id' => $user->id
                    ]);
                    continue;
                }

                $task->increment('progress', 1);
                $this->logInfo("Увеличен прогресс задачи", [
                    'task_id' => $task->task_id,
                    'user_id' => $user->id,
                    'progress' => $task->progress
                ]);

                if (!is_null($task->task->target) && $task->progress >= $task->task->target) {
                    $task->update([
                        'completed' => true,
                        'progress' => $task->task->target,
                    ]);
                    $user->increment('experience', $task->task->experience_reward);
                    $user->save();

                    $this->logInfo("Задача выполнена, начислены награды", [
                        'task_id' => $task->task_id,
                        'user_id' => $user->id,
                        'experience_reward' => $task->task->experience_reward
                    ]);
                }
                
                $task->save();
                $this->logInfo("Прогресс задачи обновлен", [
                    'task_id' => $task->task_id,
                    'progress' => $task->progress,
                    'target' => $task->task->target
                ]);
            }
            
            // Сбросить кеш задач пользователя
            $this->clearUserTasksCache($user->id);
        });
    }

    /**
     * Получить все задачи пользователя
     *
     * @param User $user Пользователь
     * @param array $filters Фильтры
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getUserTasks(User $user, $filters = [])
    {
        $cacheKey = $this->buildCacheKey('all_tasks', [$user->id, json_encode($filters)]);
        
        return $this->getFromCacheOrStore($cacheKey, $this->defaultCacheMinutes, function () use ($user, $filters) {
            $this->logInfo("Получение задач пользователя", [
                'user_id' => $user->id,
                'filters' => $filters
            ]);
            
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
        });
    }

    /**
     * Получить выполненные задачи пользователя
     *
     * @param User $user Пользователь
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getCompletedTasks(User $user)
    {
        $cacheKey = $this->buildCacheKey('completed_tasks', [$user->id]);
        
        return $this->getFromCacheOrStore($cacheKey, $this->defaultCacheMinutes, function () use ($user) {
            $this->logInfo("Получение выполненных задач пользователя", ['user_id' => $user->id]);
            return $user->tasks()->wherePivot('completed', true)->get();
        });
    }

    /**
     * Получить задачи в процессе выполнения
     *
     * @param User $user Пользователь
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getInProgressTasks(User $user)
    {
        $cacheKey = $this->buildCacheKey('in_progress_tasks', [$user->id]);
        
        return $this->getFromCacheOrStore($cacheKey, $this->defaultCacheMinutes, function () use ($user) {
            $this->logInfo("Получение задач в процессе выполнения", ['user_id' => $user->id]);
            return $user->tasks()->wherePivot('completed', false)->wherePivot('progress', '>', 0)->get();
        });
    }

    /**
     * Получить не начатые задачи
     *
     * @param User $user Пользователь
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getNotStartedTasks(User $user)
    {
        $cacheKey = $this->buildCacheKey('not_started_tasks', [$user->id]);
        
        return $this->getFromCacheOrStore($cacheKey, $this->defaultCacheMinutes, function () use ($user) {
            $this->logInfo("Получение не начатых задач", ['user_id' => $user->id]);
            return $user->tasks()->wherePivot('progress', 0)->get();
        });
    }

    /**
     * Обновить прогресс задачи
     *
     * @param User $user Пользователь
     * @param int $taskId ID задачи
     * @param int $progressIncrement Прирост прогресса
     * @return bool
     * @throws Exception
     */
    public function updateTaskProgress(User $user, $taskId, $progressIncrement)
    {
        $this->logInfo("Обновление прогресса задачи", [
            'user_id' => $user->id,
            'task_id' => $taskId,
            'progress_increment' => $progressIncrement
        ]);
        
        return $this->transaction(function () use ($user, $taskId, $progressIncrement) {
            $task = Task::find($taskId);

            if (!$task) {
                $this->logWarning("Задача не найдена", ['task_id' => $taskId]);
                throw new Exception('Task not found');
            }

            // Проверяем, существует ли задача для пользователя
            $currentTask = $user->tasks()->where('task_id', $task->id)->first();

            if (!$currentTask) {
                $this->logWarning("Задача не назначена пользователю", [
                    'user_id' => $user->id,
                    'task_id' => $taskId
                ]);
                throw new Exception('Task not found for the user');
            }

            $currentProgress = $currentTask->pivot->progress;
            $periodStart = $currentTask->pivot->period_start;
            $periodEnd = $currentTask->pivot->period_end;

            // Проверка периода выполнения задания
            if ($this->isPeriodExpired($periodEnd)) {
                $this->logWarning("Период выполнения задачи истек", [
                    'user_id' => $user->id,
                    'task_id' => $taskId,
                    'period_end' => $periodEnd
                ]);
                throw new Exception('Task period has expired');
            }

            // Проверка, что новый прогресс не меньше текущего
            if ($progressIncrement > 0) {
                $newProgress = $currentProgress + $progressIncrement;
                $completed = ($newProgress >= $task->target);

                // Синхронизация задачи
                $user->tasks()->syncWithoutDetaching([$task->id => [
                    'progress' => $newProgress,
                    'completed' => $completed,
                    'period_start' => $periodStart ?? now(),
                    'period_end' => $this->calculatePeriodEnd($task, $periodStart ?? now()),
                ]]);

                $this->logInfo("Прогресс задачи обновлен", [
                    'user_id' => $user->id,
                    'task_id' => $taskId,
                    'new_progress' => $newProgress,
                    'completed' => $completed
                ]);

                if ($completed) {
                    $this->logInfo("Задача выполнена, запуск события", [
                        'user_id' => $user->id,
                        'task_id' => $taskId
                    ]);
                    event(new TaskCompleted($user, $task));
                }
                
                // Сбросить кеш задач пользователя
                $this->clearUserTasksCache($user->id);

                return true;
            }

            $this->logWarning("Некорректное значение прогресса", [
                'user_id' => $user->id,
                'task_id' => $taskId,
                'progress_increment' => $progressIncrement
            ]);
            throw new Exception('Invalid progress value');
        });
    }

    /**
     * Проверка, истек ли период выполнения задания
     *
     * @param mixed $periodEnd Конечная дата периода
     * @return bool
     */
    private function isPeriodExpired($periodEnd)
    {
        return now() > $periodEnd;
    }

    /**
     * Рассчитать конечную дату периода
     *
     * @param Task $task Задача
     * @param mixed $periodStart Начальная дата периода
     * @return mixed
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
    
    /**
     * Очистить кеш задач пользователя
     *
     * @param int $userId ID пользователя
     * @return void
     */
    private function clearUserTasksCache(int $userId)
    {
        $this->forgetCache($this->buildCacheKey('all_tasks', [$userId, '{}']));
        $this->forgetCache($this->buildCacheKey('completed_tasks', [$userId]));
        $this->forgetCache($this->buildCacheKey('in_progress_tasks', [$userId]));
        $this->forgetCache($this->buildCacheKey('not_started_tasks', [$userId]));
    }
    
    /**
     * Назначить задачу пользователю
     *
     * @param User $user Пользователь
     * @param int $taskId ID задачи
     * @return bool
     */
    public function assignTaskToUser(User $user, int $taskId)
    {
        $this->logInfo("Назначение задачи пользователю", [
            'user_id' => $user->id,
            'task_id' => $taskId
        ]);
        
        return $this->transaction(function () use ($user, $taskId) {
            $task = Task::find($taskId);
            
            if (!$task) {
                $this->logWarning("Задача не найдена при назначении", ['task_id' => $taskId]);
                throw new Exception('Task not found');
            }
            
            // Проверка, что задача еще не назначена пользователю
            if ($user->tasks()->where('task_id', $taskId)->exists()) {
                $this->logWarning("Задача уже назначена пользователю", [
                    'user_id' => $user->id,
                    'task_id' => $taskId
                ]);
                throw new Exception('Task already assigned to user');
            }
            
            $periodStart = now();
            $user->tasks()->attach($taskId, [
                'progress' => 0,
                'completed' => false,
                'period_start' => $periodStart,
                'period_end' => $this->calculatePeriodEnd($task, $periodStart)
            ]);
            
            // Сбросить кеш задач пользователя
            $this->clearUserTasksCache($user->id);
            
            $this->logInfo("Задача успешно назначена пользователю", [
                'user_id' => $user->id,
                'task_id' => $taskId
            ]);
            
            return true;
        });
    }
}
