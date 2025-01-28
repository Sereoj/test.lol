<?php

namespace App\Listeners;

use App\Events\TaskCreated;
use App\Helpers\UserTaskHelper;
use App\Models\User;

class AddTaskToUsers
{
    public function __construct()
    {
        //
    }

    public function handle(TaskCreated $event)
    {
        $task = $event->task;

        $users = User::all();

        foreach ($users as $user) {
            $user->tasks()->syncWithoutDetaching([$task->id => [
                'progress' => 0,
                'completed' => false,
                'period_start' => now(),
                'period_end' => UserTaskHelper::calculatePeriodEnd($task, now()),
            ]]);
        }
    }
}
