<?php

namespace App\Listeners\Challenges;

use App\Events\Challenges\SubmissionCreated;
use App\Notifications\Challenges\NewSubmissionNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class NotifyOrganizerOnSubmission implements ShouldQueue
{
    use InteractsWithQueue;

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
    public function handle(SubmissionCreated $event): void
    {
        $challenge = $event->challenge;
        $post = $event->post;

        // Отправляем уведомление организатору о новой работе
        $challenge->organizer->notify(
            new NewSubmissionNotification($challenge, $post)
        );
    }
}
