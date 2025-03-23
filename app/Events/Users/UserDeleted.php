<?php

namespace App\Events\Users;

use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Событие удаления пользователя
 */
class UserDeleted
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Пользователь, который был удален
     *
     * @var User
     */
    public $user;

    /**
     * Данные пользователя перед удалением
     *
     * @var array
     */
    public $userData;

    /**
     * Создание нового экземпляра события
     *
     * @param User $user
     * @return void
     */
    public function __construct(User $user)
    {
        $this->user = $user;
        // Сохраняем основные данные пользователя перед удалением
        $this->userData = [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'created_at' => $user->created_at,
            'updated_at' => $user->updated_at,
        ];
    }
} 