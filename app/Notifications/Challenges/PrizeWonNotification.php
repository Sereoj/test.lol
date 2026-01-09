<?php

namespace App\Notifications\Challenges;

use App\Models\Challenge;
use App\Models\ChallengeWinner;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class PrizeWonNotification extends Notification implements ShouldBroadcast
{
    use Queueable;

    protected Challenge $challenge;
    protected ChallengeWinner $winner;

    /**
     * Create a new notification instance.
     */
    public function __construct(Challenge $challenge, ChallengeWinner $winner)
    {
        $this->challenge = $challenge;
        $this->winner = $winner;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'challenge_id' => $this->challenge->id,
            'challenge_title' => $this->challenge->title,
            'place' => $this->winner->place,
            'prize_amount' => $this->winner->prize_amount,
            'prize_currency' => $this->winner->prize_currency,
            'message' => "Поздравляем! Вы заняли {$this->winner->place} место в челлендже \"{$this->challenge->title}\" и выиграли {$this->winner->prize_amount} {$this->winner->prize_currency}!",
        ];
    }

    /**
     * Get the broadcastable representation of the notification.
     */
    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->toArray($notifiable));
    }
}
