<?php

namespace App\Services;

use App\Models\User;
use App\Notifications\UserFollowedNotification;

class FollowService
{
    public function followUser($followerId, $followingId)
    {
        $follower = User::find($followerId);
        $following = User::find($followingId);

        if ($follower && $following) {
            if (! $follower->following()->where('following_id', $followingId)->exists()) {
                $follower->following()->attach($followingId);
                $following->notify(new UserFollowedNotification($follower));

                return true;
            }
        }

        return false;
    }

    public function unfollowUser($followerId, $followingId)
    {
        $follower = User::find($followerId);
        $following = User::find($followingId);

        if ($follower && $following) {
            $follower->following()->detach($followingId);

            return true;
        }

        return false;
    }

    public function getFollowers($userId)
    {
        $user = User::find($userId);
        if ($user) {
            return $user->followers;
        }

        return collect();
    }

    public function getFollowing($userId)
    {
        $user = User::find($userId);
        if ($user) {
            return $user->following;
        }

        return collect();
    }
}
