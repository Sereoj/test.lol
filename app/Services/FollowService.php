<?php

namespace App\Services;

use App\Models\User;
use App\Notifications\UserFollowedNotification;
use Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FollowService
{
    public function followUser($followerId, $followingId)
    {
        try {
            DB::beginTransaction();

            $follower = User::find($followerId);
            $following = User::find($followingId);

            if ($follower && $following) {
                if (!$follower->following()->where('following_id', $followingId)->exists()) {
                    $follower->following()->attach($followingId);
                    $following->notify(new UserFollowedNotification($follower));

                    DB::commit();
                    return true;
                }
            }

            DB::rollBack();
            return false;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error following user: ' . $e->getMessage());
            return false;
        }
    }

    public function unfollowUser($followerId, $followingId)
    {
        try {
            DB::beginTransaction();

            $follower = User::find($followerId);
            $following = User::find($followingId);

            if ($follower && $following) {
                $follower->following()->detach($followingId);

                DB::commit();
                return true;
            }

            DB::rollBack();
            return false;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error unfollowing user: ' . $e->getMessage());
            return false;
        }
    }

    public function getFollowers($userId)
    {
        $user = User::find($userId);
        return $user ? $user->followers : collect();
    }

    public function getFollowing()
    {
        return Auth::user()->following ?? collect();
    }
}
