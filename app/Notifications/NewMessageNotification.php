<?php

namespace App\Notifications;

use App\Models\Messaging\Message;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewMessageNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected Message $message;

    public function __construct(Message $message)
    {
        $this->message = $message;
    }

    public function via(object $notifiable): array
    {
        $channels = [];
        if ($notifiable->notificationSettings && $notifiable->notificationSettings->email_enabled && $notifiable->notificationSettings->notify_on_new_message) {
            $channels[] = 'mail';
        }
        $channels[] = 'database';
        return $channels;
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
                    ->line('Вы получили новое сообщение от ' . $this->message->user->username)
                    ->action('Посмотреть сообщение', url('/messages/' . $this->message->conversation_id))
                    ->line('Спасибо за использование нашего приложения!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'message_id' => $this->message->id,
            'conversation_id' => $this->message->conversation_id,
            'sender_id' => $this->message->user_id,
            'sender_username' => $this->message->user->username,
            'content' => $this->message->content,
            'type' => 'new_message',
        ];
    }
} 