<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

/**
 * @OA\Schema(
 *     schema="UserNotificationSettingsResource",
 *     type="object",
 *     title="UserNotificationSettings Resource",
 *     @OA\Property(
 *         property="email_enabled",
 *         type="string",
 *         description="Email enabled"
 *     ),
 *     @OA\Property(
 *         property="push_enabled",
 *         type="string",
 *         description="Push enabled"
 *     ),
 *     @OA\Property(
 *         property="notify_on_new_message",
 *         type="string",
 *         description="Notify on new message"
 *     ),
 *     @OA\Property(
 *         property="notify_on_new_follower",
 *         type="string",
 *         description="Notify on new follower"
 *     ),
 *     @OA\Property(
 *         property="notify_on_post_like",
 *         type="string",
 *         description="Notify on post like"
 *     ),
 *     @OA\Property(
 *         property="notify_on_comment",
 *         type="string",
 *         description="Notify on comment"
 *     ),
 *     @OA\Property(
 *         property="notify_on_comment_like",
 *         type="string",
 *         description="Notify on comment like"
 *     ),
 *     @OA\Property(
 *         property="notify_on_mention",
 *         type="string",
 *         description="Notify on mention"
 *     )
 * )
 */
class UserNotificationSettingsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
        "email_enabled" => $this->email_enabled,
        "push_enabled" => $this->push_enabled,
        "notify_on_new_message" => $this->notify_on_new_message,
        "notify_on_new_follower" => $this->notify_on_new_follower,
        "notify_on_post_like" => $this->notify_on_post_like,
        "notify_on_comment" => $this->notify_on_comment,
        "notify_on_comment_like" => $this->notify_on_comment_like,
        "notify_on_mention" => $this->notify_on_mention,
        ];
    }
}
