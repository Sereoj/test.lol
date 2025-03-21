<?php

namespace App\Events;

use App\Models\Users\User;
use Illuminate\Queue\SerializesModels;

class UserExperienceChanged
{
    use SerializesModels;

    public User $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }
}
