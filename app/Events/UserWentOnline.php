<?php

namespace App\Events;

use App\Models\Users\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserWentOnline
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public User $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function broadcastOn()
    {
        return new PresenceChannel('online-users');
    }

    public function broadcastAs()
    {
        return 'user.online';
    }
}
