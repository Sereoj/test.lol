<?php

namespace App\Services\Users;

use App\Events\ProfileComplected;
use App\Models\Users\User;
use Auth;

class UserProfileService
{
    protected UserFollowService $userFollowService;

    public function __construct(UserFollowService $userFollowService)
    {
        $this->userFollowService = $userFollowService;
    }

    public function getUserProfile(string $slug): array
    {
        $authUser = Auth::user();
        $user = User::where('slug', $slug)->firstOrFail();

        $isMyProfile = $authUser && $authUser->id === $user->id;

        $followersCount = $this->userFollowService->getFollowers($user->id)->count();
        $followingCount = $this->userFollowService->getFollowing()->count();

        $isFollowing = false;
        $isFollowedBy = false;

        if ($authUser) {
            $isFollowing = $this->userFollowService->isFollowing($authUser->id, $user->id);
            $isFollowedBy = $this->userFollowService->isFollowing($user->id, $authUser->id);
        }

        return [
            'is_my_profile' => $isMyProfile,
            'user' => [
                'id' => $user->id,
                'username' => $user->username,
                'slug' => $user->slug,
                'avatars' => $user->avatars,
                'online' => $user->onlineStatus,
                'description' => $user->description,
                'verification' => $user->verification,
                'badges' => $user->badges,
                'gender' => $user->gender,
                'age' => $user->age,
                'language' => $user->language,
                'experience' => $user->experience,
                'level' => $user->level,
                'roles' => $user->role,
                'status' => $user->status,
                'location' => $user->location,
                'employment_status' => $user->employmentStatus,
                'usingApps' => $user->usingApps,
                'specializations' => $user->specializations,
                'followers_count' => $followersCount,
                'following_count' => $followingCount,
            ],
            'relationship' => [
                'is_following' => $isFollowing,
                'is_followed_by' => $isFollowedBy,
            ],
            'additional_data' => $isMyProfile ? [
                'tasks' => $user->tasks,
                'balance' => $user->userBalance,
            ] : null,
        ];
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
