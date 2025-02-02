<?php

namespace App\Listeners;

use App\Events\PostPublished;
use App\Models\UserTask;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class UpdateUserTasksOnPostPublished
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(PostPublished $event)
    {
        $post = $event->post;
        $user = $post->user;

        Log::info("Post: $post");

        $this->processTasks($user, 'publish_posts');
        //$this->processTasks($user, 'sell_images');
        //$this->processTasks($user, 'sell_images');

        // Обработка задач типа 'complete_profile'
        //$this->processTasks($user, 'complete_profile');
    }

    /**
     * @param mixed $user
     * @return void
     */
    protected function processTasks($user, $taskType)
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

            if (!is_null($task->task->target) && $task->progress >= $task->task->target) {
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
}
