<?php

namespace App\Services\Users;

use App\Events\ProfileComplected;
use App\Http\Resources\AppResource;
use App\Http\Resources\AvatarResource;
use App\Http\Resources\BadgeResource;
use App\Http\Resources\EmploymentStatusResource;
use App\Http\Resources\LevelResource;
use App\Http\Resources\LocationResource;
use App\Http\Resources\OnlineStatusResource;
use App\Http\Resources\RoleResource;
use App\Http\Resources\SpecializationResource;
use App\Http\Resources\StatusResource;
use App\Models\Users\User;
use Auth;
use Illuminate\Support\Facades\Log;
use PHPUnit\Framework\Exception;

class UserProfileService
{
    protected UserFollowService $userFollowService;

    public function __construct(UserFollowService $userFollowService)
    {
        $this->userFollowService = $userFollowService;
    }

    public function checkUser(?User $user): bool
    {
        return (bool)$user;
    }

    public function getUserProfile(string $slug): array
    {
        $authUser = Auth::guard('api')->user();
        $user = User::where('slug', $slug)->first();

        if(!$user)
        {
            throw new Exception('User not found');
        }

        Log::info('Auth user ID', ['auth_user_id' => $authUser?->id]);
        Log::info('User ID from DB', ['user_id_from_db' => $user->id]);

        $isMyProfile = $authUser->id === $user->id;

        Log::info('Is my profile', ['is_my_profile' => $isMyProfile]);

        $followersCount = $this->userFollowService->getFollowers($user->id)->count();
        $followingCount = $this->userFollowService->getFollowing()->count();

        $isFollowing = false;
        $isFollowedBy = false;

        if ($authUser) {
            $isFollowing = $this->userFollowService->isFollowing($authUser->id, $user->id);
            $isFollowedBy = $this->userFollowService->isFollowing($user->id, $authUser->id);
        }

        $activeBadge = $user->badges->firstWhere('is_active', true);

        return [
            'is_my_profile' => $isMyProfile,
            'user' => [
                'id' => $user->id,
                'username' => $user->username,
                'slug' => $user->slug,
                'avatars' => AvatarResource::collection($user->avatars->sortDesc()),
                'cover' => 'http://test/public/'.$user->cover,
                'website' => $user->website,
                'description' => $user->description,
                'verification' => $user->verification,
                'online' => new OnlineStatusResource($user->onlineStatus),
                'badges' => new BadgeResource($activeBadge),
                'gender' => $user->gender,
                'age' => $user->age,
                'language' => $user->language,
                'experience' => $user->experience,
                'level' => new LevelResource($user->level),
                'roles' => new RoleResource($user->role),
                'status' => new StatusResource($user->status),
                'location' => new LocationResource($user->location),
                'employment_status' => new EmploymentStatusResource($user->employmentStatus),
                'usingApps' => $user->usingApps,
                'specializations' => SpecializationResource::collection($user->specializations),
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
