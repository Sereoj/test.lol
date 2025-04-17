<?php

namespace App\Services\Messaging;

use App\Events\NewMessageReceived;
use App\Models\Messaging\Message;
use App\Notifications\NewMessageNotification;
use App\Repositories\MessageRepository;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class MessageService
{
    protected MessageRepository $messageRepository;

    public function __construct(MessageRepository $messageRepository)
    {
        $this->messageRepository = $messageRepository;
    }

    public function getAll(array $filters = []): \Illuminate\Database\Eloquent\Collection
    {
        return $this->messageRepository->getAll($filters);
    }

    public function create(array $data): Message
    {
        $message = $this->messageRepository->create($data);
        Log::info('Message created successfully', ['message_id' => $message->id]);
        
        // Вызов события для вещания через Reverb
        event(new NewMessageReceived($message));
        
        // Определение получателя сообщения
        $recipient = $message->conversation->creator_id === $message->user_id 
            ? $message->conversation->recipient 
            : $message->conversation->creator;
        
        // Отправка уведомления получателю
        if ($recipient) {
            Notification::send($recipient, new NewMessageNotification($message));
            Log::info('Notification sent for new message', ['message_id' => $message->id, 'recipient_id' => $recipient->id]);
        }
        
        return $message;
    }

    public function getById(int $id): ?Message
    {
        return $this->messageRepository->findById($id);
    }

    public function updateMessage(int $id, array $data): bool
    {
        $message = $this->getById($id);
        return $message ? $this->messageRepository->update($message, $data) : false;
    }

    public function deleteMessage(Message $message): void
    {
        $this->messageRepository->delete($message);
        Log::info('Message deleted successfully', ['message_id' => $message->id]);
    }

    public function getMessagesByConversationId(int $conversationId): \Illuminate\Database\Eloquent\Collection
    {
        return $this->messageRepository->getMessagesByConversationId($conversationId);
    }

    public function markAsRead(int $messageId): ?Message
    {
        $message = $this->getById($messageId);
        if ($message) {
            $message->update(['read' => true]);
            Log::info('Message marked as read', ['message_id' => $messageId]);
        }
        return $message;
    }
} 