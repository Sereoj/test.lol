<?php

namespace App\Services\Messaging;

use App\Models\Messaging\Conversation;
use App\Repositories\ConversationRepository;
use Illuminate\Support\Facades\Log;

class ConversationService
{
    protected ConversationRepository $conversationRepository;

    public function __construct(ConversationRepository $conversationRepository)
    {
        $this->conversationRepository = $conversationRepository;
    }

    public function getAll(array $filters = []): \Illuminate\Database\Eloquent\Collection
    {
        return $this->conversationRepository->getAll($filters);
    }

    public function create(array $data): Conversation
    {
        $conversation = $this->conversationRepository->create($data);
        Log::info('Conversation created successfully', ['conversation_id' => $conversation->id]);
        return $conversation;
    }

    public function getById(int $id): ?Conversation
    {
        return $this->conversationRepository->findById($id);
    }

    public function updateConversation(int $id, array $data): bool
    {
        $conversation = $this->getById($id);
        return $conversation && $this->conversationRepository->update($conversation, $data);
    }

    public function deleteConversation(Conversation $conversation): void
    {
        $this->conversationRepository->delete($conversation);
        Log::info('Conversation deleted successfully', ['conversation_id' => $conversation->id]);
    }

    public function getConversationsByUserId(int $userId): \Illuminate\Database\Eloquent\Collection
    {
        return $this->conversationRepository->getConversationsByUserId($userId);
    }
}
