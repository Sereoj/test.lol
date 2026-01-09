<?php

namespace App\Listeners\Challenges;

use App\Events\Challenges\ChallengeStarted;
use App\Notifications\Challenges\ChallengeStartedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class NotifyParticipantsOnStart implements ShouldQueue
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
    public function handle(ChallengeStarted $event): void
    {
        $challenge = $event->challenge;

        // Получаем всех участников челленджа
        $participants = $challenge->participants;

        // Отправляем уведомление каждому участнику
        foreach ($participants as $participant) {
            $participant->notify(
                new ChallengeStartedNotification($challenge)
            );
        }
    }
}
