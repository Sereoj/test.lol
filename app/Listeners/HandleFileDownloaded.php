<?php

namespace App\Listeners;

use App\Events\FileDownloaded;
use App\Services\UserTaskService;
use Illuminate\Support\Facades\Log;

class HandleFileDownloaded
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
    public function handle(FileDownloaded $event): void
    {
        $media = $event->media;
        $user = $media->user;

        Log::info("File downloaded: $media");
        $this->taskService->processTasks($user, 'download_files');
    }
}
