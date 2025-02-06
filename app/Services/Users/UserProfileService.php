<?php

namespace App\Services\Users;

use App\Events\ProfileComplected;
use App\Models\Users\User;

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

            if ($user->isProfileComplete()) {
                event(new ProfileComplected($user));
            }

            //event(new UserExperienceChanged($user));
            return $user;
        }

        return null;
    }
}
