<?php

namespace App\Notifications\Challenges;

use App\Models\Challenge;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class ChallengeStartedNotification extends Notification implements ShouldBroadcast
{
    use Queueable;

    protected Challenge $challenge;

    /**
     * Create a new notification instance.
     */
    public function __construct(Challenge $challenge)
    {
        $this->challenge = $challenge;
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
            'start_date' => $this->challenge->start_date?->toIso8601String(),
            'end_date' => $this->challenge->end_date?->toIso8601String(),
            'message' => "Челлендж \"{$this->challenge->title}\" начался! Успейте подать работу до окончания.",
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
