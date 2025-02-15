<?php

namespace App\Http\Resources;

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
            'email' => $this->email,
            'level' => new LevelResource($this->level),
            'achievements' => AchievementResource::collection($this->achievements),
            'role' => new RoleResource($this->role),
            'badges' => BadgeResource::collection($this->badges),
            'using_app' => new AppResource($this->usingApps),
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
