<?php

namespace App\Events\Users;

use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Событие создания пользователя
 */
class UserCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Пользователь, который был создан
     *
     * @var User
     */
    public $user;

    /**
     * Создание нового экземпляра события
     *
     * @param User $user
     * @return void
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }
} 