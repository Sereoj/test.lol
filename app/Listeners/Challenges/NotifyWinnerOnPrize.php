<?php

namespace App\Listeners\Challenges;

use App\Events\Challenges\PrizeDistributed;
use App\Notifications\Challenges\PrizeWonNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class NotifyWinnerOnPrize implements ShouldQueue
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
    public function handle(PrizeDistributed $event): void
    {
        $challenge = $event->challenge;
        $winner = $event->winner;

        // Отправляем уведомление победителю о выигрыше
        $winner->user->notify(
            new PrizeWonNotification($challenge, $winner)
        );
    }
}
