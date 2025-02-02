<?php

namespace App\Listeners;

use App\Events\ProfileComplected;
use App\Services\UserTaskService;
use Illuminate\Support\Facades\Log;

class HandleProfileComplected
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
    public function handle(ProfileComplected $event): void
    {
        $user = $event->user;

        Log::info("Profile complected: $user");
        $this->userTaskService->processTasks($user, 'publish_posts');
    }
}
