<?php

namespace App\Repositories;

use App\Models\Messaging\Conversation;
use Illuminate\Database\Eloquent\Collection;

class ConversationRepository
{
    protected Conversation $model;

    public function __construct(Conversation $model)
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

    public function create(array $data): Conversation
    {
        return $this->model->create($data);
    }

    public function findById(int $id): ?Conversation
    {
        return $this->model->find($id);
    }

    public function update(Conversation $conversation, array $data): bool
    {
        return $conversation->update($data);
    }

    public function delete(Conversation $conversation): ?bool
    {
        return $conversation->delete();
    }

    public function getConversationsByUserId(int $userId): Collection
    {
        return $this->model->where('creator_id', $userId)
            ->orWhere('recipient_id', $userId)
            ->get();
    }
} 