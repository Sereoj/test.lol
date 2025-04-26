<?php

namespace App\Services;

use App\Events\NewMessageReceived;
use App\Models\Users\User;
use App\Notifications\NewMessageNotification;
use App\Services\Messaging\ConversationService;
use App\Services\Messaging\MessageService;
use Exception;
use Illuminate\Support\Facades\Log;
use Laravel\Reverb\Events\MessageReceived;
use Symfony\Component\Mailer\Event\MessageEvent;

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

    public function sendMessage(int $creatorId, int $recipientId, string $content = 'Добро пожаловать! Мы рады видеть вас на нашем сайте. Если у вас есть вопросы, не стесняйтесь обращаться.')
    {
        try {
            $conversation = $this->conversationService->create([
                'creator_id' => $creatorId, // Администратор
                'recipient_id' => $recipientId, // Новый пользователь
                'last_message_at' => now()
            ]);

            // Отправляем приветственное сообщение от имени администратора
            $message = $this->messageService->create([
                'user_id' => 1, // Администратор
                'conversation_id' => $conversation->id,
                'content' => $content,
                'read' => false
            ]);
            event(new NewMessageReceived($message));

            $conversation->update(['last_message_at' => now()]);

            Log::info('Приветственное сообщение отправлено новому пользователю', [
                'user_id' => $recipientId,
                'admin_id' => $creatorId
            ]);

            return $message;
        }catch (Exception $exception){
            throw new Exception($exception->getMessage());
        }
    }
}
