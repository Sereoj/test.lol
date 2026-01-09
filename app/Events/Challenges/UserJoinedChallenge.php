<?php

namespace App\Events\Challenges;

use App\Models\Challenge;
use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserJoinedChallenge
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public Challenge $challenge;
    public User $user;

    public function __construct(Challenge $challenge, User $user)
    {
        $this->challenge = $challenge;
        $this->user = $user;
    }
}
