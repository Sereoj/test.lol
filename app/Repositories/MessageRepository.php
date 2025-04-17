<?php

namespace App\Repositories;

use App\Models\Messaging\Message;
use Illuminate\Database\Eloquent\Collection;

class MessageRepository
{
    protected Message $model;

    public function __construct(Message $model)
    {
        $this->model = $model;
    }

    public function getAll(array $filters = []): Collection
    {
        $query = $this->model->query();

        if (!empty($filters)) {
            foreach ($filters as $key => $value) {
                if (!empty($value)) {
                    $query->where($key, $value);
                }
            }
        }

        return $query->get();
    }

    public function create(array $data): Message
    {
        return $this->model->create($data);
    }

    public function findById(int $id): ?Message
    {
        return $this->model->find($id);
    }

    public function update(Message $message, array $data): bool
    {
        return $message->update($data);
    }

    public function delete(Message $message): ?bool
    {
        return $message->delete();
    }

    public function getMessagesByConversationId(int $conversationId): Collection
    {
        return $this->model->where('conversation_id', $conversationId)
            ->orderBy('created_at', 'asc')
            ->get();
    }
} 