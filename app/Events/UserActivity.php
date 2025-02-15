<?php

namespace App\Events;

use App\Models\Users\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserActivity
{
    use Dispatchable, SerializesModels;

    public User $user;

    public string $deviceType;

    public string $ipAddress;

    public function __construct(User $user, $deviceType = null, $ipAddress = null)
    {
        $this->user = $user;
        $this->deviceType = $deviceType;
        $this->ipAddress = $ipAddress;
    }
}
