<?php

namespace App\Http\Controllers;

use App\Http\Requests\Message\SendMessageRequest;
use App\Services\Messaging\ConversationService;
use App\Services\Messaging\MessageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/**
 * @group Сообщения
 *
 * API для работы с личными сообщениями между пользователями
 */
class MessageController extends Controller
{
    protected MessageService $messageService;
    protected ConversationService $conversationService;

    public function __construct(MessageService $messageService, ConversationService $conversationService)
    {
        $this->messageService = $messageService;
        $this->conversationService = $conversationService;
        $this->middleware('auth:api');
    }

    /**
     * Получить список всех чатов пользователя.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $userId = Auth::id();
            $conversations = $this->conversationService->getConversationsByUserId($userId);
            return $this->successResponse($conversations);
        } catch (\Exception $e) {
            Log::error('Ошибка при получении списка чатов: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'exception' => $e->getTraceAsString()
            ]);
            return $this->errorResponse('Не удалось получить список чатов', 500);
        }
    }

    /**
     * Получить список чатов пользователя.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getChats(Request $request): JsonResponse
    {
        try {
            $userId = Auth::id();
            $conversations = $this->conversationService->getConversationsByUserId($userId);
            return $this->successResponse($conversations);
        } catch (\Exception $e) {
            Log::error('Ошибка при получении списка чатов: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'exception' => $e->getTraceAsString()
            ]);
            return $this->errorResponse('Не удалось получить список чатов', 500);
        }
    }

    /**
     * Получить сообщения с конкретным пользователем.
     *
     * @param int $userId
     * @param Request $request
     * @return JsonResponse
     */
    public function getMessages(int $userId, Request $request): JsonResponse
    {
        try {
            $currentUserId = Auth::id();
            $conversation = $this->conversationService->getAll([
                'creator_id' => $currentUserId,
                'recipient_id' => $userId
            ])->first() ?? $this->conversationService->getAll([
                'creator_id' => $userId,
                'recipient_id' => $currentUserId
            ])->first();

            if (!$conversation) {
                return $this->errorResponse('Беседа не найдена', 404);
            }

            $messages = $this->messageService->getMessagesByConversationId($conversation->id);
            return $this->successResponse($messages);
        } catch (\Exception $e) {
            Log::error('Ошибка при получении сообщений: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'recipient_id' => $userId,
                'exception' => $e->getTraceAsString()
            ]);
            return $this->errorResponse('Не удалось получить сообщения', 500);
        }
    }

    /**
     * Отправить сообщение пользователю.
     *
     * @param int $userId
     * @param SendMessageRequest $request
     * @return JsonResponse
     */
    public function sendMessage(int $userId, SendMessageRequest $request): JsonResponse
    {
        try {
            $currentUserId = Auth::id();
            $conversation = $this->conversationService->getAll([
                'creator_id' => $currentUserId,
                'recipient_id' => $userId
            ])->first() ?? $this->conversationService->getAll([
                'creator_id' => $userId,
                'recipient_id' => $currentUserId
            ])->first();

            if (!$conversation) {
                $conversation = $this->conversationService->create([
                    'creator_id' => $currentUserId,
                    'recipient_id' => $userId,
                    'last_message_at' => now()
                ]);
            }

            $message = $this->messageService->create([
                'user_id' => $currentUserId,
                'conversation_id' => $conversation->id,
                'content' => $request->input('content'),
                'read' => false
            ]);

            // Обновление времени последнего сообщения в беседе
            $conversation->update(['last_message_at' => now()]);

            return $this->successResponse($message, 201);
        } catch (\Exception $e) {
            Log::error('Ошибка при отправке сообщения: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'recipient_id' => $userId,
                'exception' => $e->getTraceAsString()
            ]);
            return $this->errorResponse('Не удалось отправить сообщение', 500);
        }
    }

    /**
     * Отметить сообщения с пользователем как прочитанные.
     *
     * @param int $userId
     * @param Request $request
     * @return JsonResponse
     */
    public function markAsRead(int $userId, Request $request): JsonResponse
    {
        try {
            $currentUserId = Auth::id();
            $conversation = $this->conversationService->getAll([
                'creator_id' => $currentUserId,
                'recipient_id' => $userId
            ])->first() ?? $this->conversationService->getAll([
                'creator_id' => $userId,
                'recipient_id' => $currentUserId
            ])->first();

            if (!$conversation) {
                return $this->errorResponse('Беседа не найдена', 404);
            }

            $messages = $this->messageService->getMessagesByConversationId($conversation->id)
                ->where('user_id', '!=', $currentUserId)
                ->where('read', false);

            foreach ($messages as $message) {
                $this->messageService->markAsRead($message->id);
            }

            return $this->successResponse(['message' => 'Сообщения отмечены как прочитанные']);
        } catch (\Exception $e) {
            Log::error('Ошибка при отметке сообщений как прочитанных: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'recipient_id' => $userId,
                'exception' => $e->getTraceAsString()
            ]);
            return $this->errorResponse('Не удалось отметить сообщения как прочитанные', 500);
        }
    }
}
