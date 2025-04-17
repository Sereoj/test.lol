<?php

namespace App\Events;

use App\Models\Messaging\Message;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Событие для уведомления о новом полученном сообщении.
 * Это событие вещается через Laravel Reverb для мгновенного уведомления пользователей.
 */
class NewMessageReceived implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Message $message;

    public function __construct(Message $message)
    {
        $this->message = $message;
    }

    /**
     * Определение каналов для вещания события.
     * Используются приватные каналы для каждого участника беседы.
     *
     * @return array<Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('user.' . $this->message->conversation->recipient_id),
            new PrivateChannel('user.' . $this->message->conversation->creator_id),
        ];
    }

    /**
     * Имя события для вещания.
     *
     * @return string
     */
    public function broadcastAs(): string
    {
        return 'new-message-received';
    }

    /**
     * Данные, которые будут переданы в событии.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'message_id' => $this->message->id,
            'conversation_id' => $this->message->conversation_id,
            'sender_id' => $this->message->user_id,
            'content' => $this->message->content,
            'created_at' => $this->message->created_at->toISOString(),
        ];
    }
} 