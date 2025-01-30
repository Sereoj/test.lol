<?php

namespace App\Services;

use App\Events\UserExperienceChanged;
use App\Models\User;

class UserProfileService
{
    public function getUserProfile($userId)
    {
        return User::find($userId);
    }

    public function updateUserProfile($userId, array $data)
    {
        $user = User::find($userId);
        if ($user) {
            $user->update($data);

            //event(new UserExperienceChanged($user));
            return $user;
        }

        return null;
    }
}
