<?php

namespace App\Repositories;

use App\Models\Avatar;

class AvatarRepository
{
    public function createAvatar(array $data)
    {
        return Avatar::create([
            'user_id' => $data['user_id'],
            'path' => $data['path'],
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

    public function deleteAvatar($avatar)
    {
        return $avatar->delete();
    }
}
