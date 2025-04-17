<?php

namespace App\Services;

use App\Models\Users\User;
use App\Services\Messaging\ConversationService;
use App\Services\Messaging\MessageService;
use Illuminate\Support\Facades\Log;

class UserMessageService
{
    protected ConversationService $conversationService;
    protected MessageService $messageService;

    public function __construct(ConversationService $conversationService,
        MessageService $messageService)
    {
        $this->messageService = $messageService;
        $this->conversationService = $conversationService;
    }

    public function sendMessage(int $creatorId,
                                  int $recipientId,
                                  string $message = 'Добро пожаловать! Мы рады видеть вас на нашем сайте. Если у вас есть вопросы, не стесняйтесь обращаться.')
    {
        $conversation = $this->conversationService->create([
            'creator_id' => $creatorId, // Администратор
            'recipient_id' => $recipientId, // Новый пользователь
            'last_message_at' => now()
        ]);

        // Отправляем приветственное сообщение от имени администратора
        $this->messageService->create([
            'user_id' => 1, // Администратор
            'conversation_id' => $conversation->id,
            'content' => $message,
            'read' => false
        ]);

        // Обновляем время последнего сообщения в беседе
        $conversation->update(['last_message_at' => now()]);

        Log::info('Приветственное сообщение отправлено новому пользователю', [
            'user_id' => $recipientId,
            'admin_id' => $creatorId
        ]);
    }
}
