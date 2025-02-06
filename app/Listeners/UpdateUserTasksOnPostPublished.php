<?php

namespace App\Listeners;

use App\Events\PostPublished;
use App\Services\Users\UserTaskService;
use Illuminate\Support\Facades\Log;

class UpdateUserTasksOnPostPublished
{
    protected UserTaskService $taskService;

    /**
     * Create the event listener.
     */
    public function __construct(UserTaskService $taskService)
    {
        $this->taskService = $taskService;
    }

    /**
     * Handle the event.
     */
    public function handle(PostPublished $event)
    {
        $post = $event->post;
        $user = $post->user;

        Log::info("Comment created: $post");
        $this->taskService->processTasks($user, 'publish_posts');
    }
}
