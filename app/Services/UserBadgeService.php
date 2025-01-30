<?php

namespace App\Services;

use App\Models\UserBadge;

class UserBadgeService
{
    public function getAllUserBadges()
    {
        return UserBadge::all();
    }

    public function createUserBadge(array $data)
    {
        return UserBadge::create($data);
    }

    public function getUserBadgeById($id)
    {
        return UserBadge::findOrFail($id);
    }

    public function updateUserBadge($id, array $data)
    {
        $userBadge = UserBadge::findOrFail($id);
        $userBadge->update($data);

        return $userBadge;
    }

    public function deleteUserBadge($id)
    {
        $userBadge = UserBadge::findOrFail($id);
        $userBadge->delete();

        return response()->json(['message' => 'UserBadge deleted successfully']);
    }
}
