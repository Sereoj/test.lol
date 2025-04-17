<?php

namespace App\Events;

use App\Models\Users\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NotificationSettingsUpdated
{
    use Dispatchable, SerializesModels;

    /**
     * Пользователь, чьи настройки уведомлений были обновлены.
     *
     * @var User
     */
    public $user;

    /**
     * Создает новый экземпляр события.
     *
     * @param User $user
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }
}
