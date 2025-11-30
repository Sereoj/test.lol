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
use OpenApi\Attributes as OA;

/**
 * @OA\Schema(
 *     schema="UserLongResource",
 *     type="object",
 *     title="UserLong Resource",
 *     @OA\Property(
 *         property="id",
 *         type="string",
 *         description="Id"
 *     ),
 *     @OA\Property(
 *         property="username",
 *         type="string",
 *         description="Username"
 *     ),
 *     @OA\Property(
 *         property="slug",
 *         type="string",
 *         description="Slug"
 *     ),
 *     @OA\Property(
 *         property="email",
 *         type="string",
 *         description="Email"
 *     ),
 *     @OA\Property(
 *         property="avatars",
 *         type="string",
 *         description="Avatars"
 *     ),
 *     @OA\Property(
 *         property="level",
 *         type="object",
 *         description="Level"
 *     ),
 *     @OA\Property(
 *         property="achievements",
 *         type="array",
 *         description="Achievements"
,
 *         @OA\Items(type="object")
 *     ),
 *     @OA\Property(
 *         property="role",
 *         type="object",
 *         description="Role"
 *     ),
 *     @OA\Property(
 *         property="badges",
 *         type="array",
 *         description="Badges"
,
 *         @OA\Items(type="object")
 *     ),
 *     @OA\Property(
 *         property="using_app",
 *         type="array",
 *         description="Using app"
,
 *         @OA\Items(type="object")
 *     ),
 *     @OA\Property(
 *         property="user_setting",
 *         type="object",
 *         description="User setting"
 *     ),
 *     @OA\Property(
 *         property="status",
 *         type="object",
 *         description="Status"
 *     ),
 *     @OA\Property(
 *         property="employment_status",
 *         type="object",
 *         description="Employment status"
 *     ),
 *     @OA\Property(
 *         property="location",
 *         type="object",
 *         description="Location"
 *     ),
 *     @OA\Property(
 *         property="tasks",
 *         type="array",
 *         description="Tasks"
,
 *         @OA\Items(type="object")
 *     ),
 *     @OA\Property(
 *         property="user_balance",
 *         type="array",
 *         description="User balance"
,
 *         @OA\Items(type="object")
 *     ),
 *     @OA\Property(
 *         property="online_status",
 *         type="object",
 *         description="Online status"
 *     )
 * )
 */
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
