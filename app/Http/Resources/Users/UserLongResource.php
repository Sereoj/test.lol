<?php

namespace App\Http\Resources\Users;

use App\Http\Resources\AchievementResource;
use App\Http\Resources\AppResource;
use App\Http\Resources\BadgeResource;
use App\Http\Resources\BalanceResource;
use App\Http\Resources\EmploymentStatusResource;
use App\Http\Resources\LevelResource;
use App\Http\Resources\LocationResource;
use App\Http\Resources\OnlineStatusResource;
use App\Http\Resources\RoleResource;
use App\Http\Resources\StatusResource;
use App\Http\Resources\TaskResource;
use App\Http\Resources\UserSettingResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserLongResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'username' => $this->username,
            'slug' => $this->slug,
            'email' => $this->email,
            'avatars' => $this->avatars,
            'level' => new LevelResource($this->level),
            'achievements' => AchievementResource::collection($this->achievements),
            'role' => new RoleResource($this->role),
            'badges' => BadgeResource::collection($this->badges),
            'using_app' => AppResource::collection($this->usingApps),
            'user_setting' => new UserSettingResource($this->userSettings),
            'status' => new StatusResource($this->status),
            'employment_status' => new EmploymentStatusResource($this->employmentStatus),
            'location' => new LocationResource($this->location),
            'tasks' => TaskResource::collection($this->tasks),
            'user_balance' => BalanceResource::collection($this->userBalance),
            'online_status' => new OnlineStatusResource($this->onlineStatus),
        ];
    }
}
