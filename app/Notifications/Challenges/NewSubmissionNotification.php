<?php

namespace App\Notifications\Challenges;

use App\Models\Challenge;
use App\Models\Posts\Post;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class NewSubmissionNotification extends Notification implements ShouldBroadcast
{
    use Queueable;

    protected Challenge $challenge;
    protected Post $post;

    /**
     * Create a new notification instance.
     */
    public function __construct(Challenge $challenge, Post $post)
    {
        $this->challenge = $challenge;
        $this->post = $post;
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
            'post_id' => $this->post->id,
            'author_id' => $this->post->user_id,
            'author_name' => $this->post->user->name ?? 'Unknown',
            'message' => "Новая работа добавлена в челлендж \"{$this->challenge->title}\"",
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
