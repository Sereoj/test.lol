<?php

namespace App\Services\Users;

use App\Models\Users\User;
use App\Models\Users\UserStatus;

class UserStatusService
{
    public function getAll(User $user)
    {
        return UserStatus::all() ?? collect();
    }

    public function assignStatus(User $user, int $statusId)
    {
        $user->status()->associate($statusId);
        $user->save();
        return $user->status;
    }

    public function detachStatus(User $user)
    {
        $user->status()->dissociate();
        $user->save();
        return $user->status;
    }
}
