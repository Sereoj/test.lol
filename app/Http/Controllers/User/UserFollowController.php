<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Services\FollowService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserFollowController extends Controller
{
    protected FollowService $followService;

    public function __construct(FollowService $followService)
    {
        $this->followService = $followService;
    }

    public function follow(Request $request, $userId)
    {
        $followerId = Auth::id();
        $result = $this->followService->followUser($followerId, $userId);

        if ($result) {
            return response()->json(['message' => 'User followed successfully']);
        }

        return response()->json(['message' => 'User not found'], 404);
    }

    public function unfollow(Request $request, $userId)
    {
        $followerId = Auth::id();
        $result = $this->followService->unfollowUser($followerId, $userId);

        if ($result) {
            return response()->json(['message' => 'User unfollowed successfully']);
        }

        return response()->json(['message' => 'User not found'], 404);
    }

    public function followers($userId)
    {
        $followers = $this->followService->getFollowers($userId);

        return response()->json($followers);
    }

    public function following($userId)
    {
        $following = $this->followService->getFollowing($userId);

        return response()->json($following);
    }
}
