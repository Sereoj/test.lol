<?php

namespace App\Listeners\Challenges;

use App\Events\Challenges\ChallengeCompleted;
use App\Notifications\Challenges\ChallengeCompletedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class NotifyParticipantsOnCompletion implements ShouldQueue
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
    public function handle(ChallengeCompleted $event): void
    {
        $challenge = $event->challenge;

        // Получаем всех участников челленджа
        $participants = $challenge->participants;

        // Отправляем уведомление каждому участнику
        foreach ($participants as $participant) {
            $participant->notify(
                new ChallengeCompletedNotification($challenge)
            );
        }
    }
}
