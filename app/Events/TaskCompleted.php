<?php

namespace App\Events;

use App\Models\Task;
use App\Models\User;
use Illuminate\Queue\SerializesModels;

class TaskCompleted
{
    use SerializesModels;

    public User $user;

    public Task $task;

    public function __construct(User $user, Task $task)
    {
        $this->user = $user;
        $this->task = $task;
    }
}
