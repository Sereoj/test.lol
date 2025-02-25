<?php

namespace App\Events;

use App\Models\Content\Task;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TaskCreated
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public Task $task;

    public function __construct(Task $task)
    {
        $this->task = $task;
    }
}
