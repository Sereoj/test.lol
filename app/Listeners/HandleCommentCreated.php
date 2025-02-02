<?php

namespace App\Listeners;

use App\Events\CommentCreated;
use App\Services\UserTaskService;
use Illuminate\Support\Facades\Log;

class HandleCommentCreated
{
    protected UserTaskService $userTaskService;

    /**
     * Create the event listener.
     */
    public function __construct(UserTaskService $userTaskService)
    {
        $this->userTaskService = $userTaskService;
    }

    /**
     * Handle the event.
     */
    public function handle(CommentCreated $event)
    {
        $comment = $event->comment;
        $user = $comment->user;

        Log::info("Comment created: $comment");
        $this->userTaskService->processTasks($user, 'leave_comments');
    }
}
