<?php

namespace App\Events\Users;

use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Событие обновления пользователя
 */
class UserUpdated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Пользователь, который был обновлен
     *
     * @var User
     */
    public $user;

    /**
     * Старые данные пользователя
     *
     * @var array
     */
    public $oldData;

    /**
     * Создание нового экземпляра события
     *
     * @param User $user
     * @param array|null $oldData
     * @return void
     */
    public function __construct(User $user, ?array $oldData = null)
    {
        $this->user = $user;
        $this->oldData = $oldData ?: [];
    }
} 