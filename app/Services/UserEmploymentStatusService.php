<?php

namespace App\Services;

use App\Models\EmploymentStatus;
use App\Models\User;

class UserEmploymentStatusService
{
    public function assignEmploymentStatusToUser($userId, $employmentStatusId)
    {
        $user = User::find($userId);
        $employmentStatus = EmploymentStatus::find($employmentStatusId);

        if ($user && $employmentStatus) {
            $user->employmentStatus()->associate($employmentStatus);
            $user->save();
            return $user;
        }

        return null;
    }

    public function removeEmploymentStatusFromUser($userId)
    {
        $user = User::find($userId);

        if ($user) {
            $user->employmentStatus()->dissociate();
            $user->save();
            return $user;
        }

        return null;
    }
}
