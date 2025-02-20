<?php

namespace App\Store;

class UserRelations
{
    public static function getUserRelations()
    {
        return [
            'level',
            'achievements',
            'role',
            'badges',
            'usingApps',
            'userSettings',
            'specializations',
            'status',
            'following',
            'followers',
            'employmentStatus',
            'location',
            'tasks',
            'userBalance',
            'transactions',
            'sources',
            'skills',
            'avatars',
            'onlineStatus',
        ];
    }
}
