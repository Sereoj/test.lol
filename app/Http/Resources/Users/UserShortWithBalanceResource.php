<?php

namespace App\Http\Resources\Users;

use App\Http\Resources\AvatarResource;
use App\Http\Resources\BadgeResource;
use App\Http\Resources\OnlineStatusResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

/**
 * @OA\Schema(
 *     schema="UserShortWithBalanceResource",
 *     type="object",
 *     title="UserShortWithBalance Resource",
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
 *         property="email",
 *         type="string",
 *         description="Email"
 *     ),
 *     @OA\Property(
 *         property="slug",
 *         type="string",
 *         description="Slug"
 *     ),
 *     @OA\Property(
 *         property="verification",
 *         type="string",
 *         description="Verification"
 *     ),
 *     @OA\Property(
 *         property="avatar",
 *         type="object",
 *         description="Avatar"
 *     ),
 *     @OA\Property(
 *         property="badge",
 *         type="object",
 *         description="Badge"
 *     ),
 *     @OA\Property(
 *         property="online",
 *         type="object",
 *         description="Online"
 *     ),
 *     @OA\Property(
 *         property="wallet",
 *         type="string",
 *         description="Wallet"
 *     ),
 *     @OA\Property(
 *         property="balance",
 *         type="array",
 *         description="Balance"
,
 *         @OA\Items(type="object")
 *     )
 * )
 */
class UserShortWithBalanceResource extends JsonResource
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
            'email' => $this->when($request->user() && $request->user()->id === $this->id, $this->email),
            'slug' => $this->slug,
            'verification' => $this->verification,
            'avatar' => new AvatarResource($this->currentAvatar),
            'badge' => new BadgeResource($this->bagde),
            'online' => new OnlineStatusResource($this->onlineStatus),
            'wallet' => $this->when($request->user() && $request->user()->id === $this->id, [
                'balance' => ShortUserBalance::collection($this->userBalance)
            ]),
        ];
    }
}
