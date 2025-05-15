<?php

namespace App\Repositories;

use App\Models\Media\Avatar;
use App\Models\Users\User;

class AvatarRepository
{
    public function createAvatar(array $data)
    {
        Avatar::where('user_id', $data['user_id'])->update(['is_active' => false]);
        return Avatar::create([
            'user_id' => $data['user_id'],
            'path' => $data['path'],
            'is_active' => true
        ]);
    }

    public function getUserAvatars($userId)
    {
        return Avatar::query()->where('user_id', $userId)->get();
    }

    public function findAvatarByUserIdAndId($userId, $avatarId)
    {
        return Avatar::query()
            ->where('user_id', $userId)
            ->where('id', $avatarId)
            ->firstOrFail();
    }

    public function setActive(User $user, int $avatarId)
    {
        Avatar::where('user_id', $user->id)->update(['is_active' => false]);
        return Avatar::where('user_id', $user->id)
            ->where('id', $avatarId)->update(['is_active' => true]);
    }
    public function deleteAvatar($avatar)
    {
        return $avatar->delete();
    }
}
