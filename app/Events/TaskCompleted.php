<?php

namespace App\Events;

use App\Models\Content\Task;
use App\Models\Users\User;
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
