<?php

namespace App\Services\Users;

use App\Models\Users\User;
use App\Models\Users\UserStatus;
use Illuminate\Support\Facades\Auth;

class UserStatusService
{
    public function getAll()
    {
        return [
            'active_status' => Auth::user()->status,
            'statuses' => UserStatus::all()
        ];
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
