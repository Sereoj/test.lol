<?php

namespace App\Http\Controllers;

use App\Services\Messaging\ConversationService;
use App\Services\Messaging\MessageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

/**
 * @group Сообщения
 *
 * API для работы с личными сообщениями между пользователями
 */
class MessageController extends Controller
{
    public function __construct(
        protected MessageService $messageService,
        protected ConversationService $conversationService
    ) {
        $this->middleware('auth:api');
    }

    /**
     * Получить список всех чатов пользователя.
     */
    public function index(): JsonResponse
    {
        try {
            $messages = $this->messageService->getAllMessages(auth()->id());
            return $this->successResponse(
                'Messages retrieved successfully',
                new MessageCollection($messages)
            );
        } catch (\Exception $e) {
            Log::error('Error retrieving messages: ' . $e->getMessage(), [
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);
            return $this->errorResponse('Error retrieving messages');
        }
    }

    /**
     * Получить список чатов пользователя.
     */
    public function getChats(): JsonResponse
    {
        try {
            $chats = $this->conversationService->getUserChats(auth()->id());
            return $this->successResponse(
                'Chats retrieved successfully',
                $chats
            );
        } catch (\Exception $e) {
            Log::error('Error retrieving chats: ' . $e->getMessage(), [
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);
            return $this->errorResponse('Error retrieving chats');
        }
    }

    /**
     * Получить сообщения с конкретным пользователем.
     *
     * @param int $conversationId
     */
    public function getMessages(int $conversationId): JsonResponse
    {
        try {
            $messages = $this->messageService->getConversationMessages($conversationId, auth()->id());
            return $this->successResponse(
                'Messages retrieved successfully',
                new MessageCollection($messages)
            );
        } catch (\Exception $e) {
            Log::error('Error retrieving conversation messages: ' . $e->getMessage(), [
                'conversation_id' => $conversationId,
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);
            return $this->errorResponse('Error retrieving conversation messages');
        }
    }

    /**
     * Отправить сообщение пользователю.
     *
     * @param SendMessageRequest $request
     */
    public function store(SendMessageRequest $request): JsonResponse
    {
        try {
            $message = $this->messageService->create([
                'conversation_id' => $request->conversation_id,
                'sender_id' => auth()->id(),
                'message' => $request->message,
                'attachments' => $request->attachments
            ]);

            return $this->successResponse(
                'Message sent successfully',
                new MessageResource($message)
            );
        } catch (\Exception $e) {
            Log::error('Error sending message: ' . $e->getMessage(), [
                'conversation_id' => $request->conversation_id,
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);
            return $this->errorResponse('Error sending message');
        }
    }

    /**
     * Отметить сообщения с пользователем как прочитанные.
     * @param int $messageId
     */
    public function markAsRead(int $messageId): JsonResponse
    {
        try {
            $message = $this->messageService->markAsRead($messageId, auth()->id());

            if (!$message) {
                return $this->errorResponse('Message not found', 404);
            }

            return $this->successResponse(
                'Message marked as read successfully',
                new MessageResource($message)
            );
        } catch (\Exception $e) {
            Log::error('Error marking message as read: ' . $e->getMessage(), [
                'message_id' => $messageId,
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);
            return $this->errorResponse('Error marking message as read');
        }
    }

    /**
     * Удалить сообщение.
     */
    public function delete(int $messageId): JsonResponse
    {
        try {
            $result = $this->messageService->delete($messageId, auth()->id());

            if (!$result) {
                return $this->errorResponse('Message not found', 404);
            }

            return $this->successResponse('Message deleted successfully');
        } catch (\Exception $e) {
            Log::error('Error deleting message: ' . $e->getMessage(), [
                'message_id' => $messageId,
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);
            return $this->errorResponse('Error deleting message');
        }
    }
}
