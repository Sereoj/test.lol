<?php

namespace App\Notifications;

use App\Models\Posts\Post;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class PostCollaboratorAddedNotification extends Notification implements ShouldBroadcast
{
    use Queueable;

    protected Post $post;

    public function __construct(Post $post)
    {
        $this->post = $post;
    }

    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'post_id' => $this->post->id,
            'post_title' => $this->post->title,
            'post_slug' => $this->post->slug,
            'author_id' => $this->post->user_id,
            'author_username' => $this->post->user->username ?? 'Unknown',
            'message' => "{$this->post->user->username} добавил вас в соавторы поста \"{$this->post->title}\"",
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->toArray($notifiable));
    }
}
