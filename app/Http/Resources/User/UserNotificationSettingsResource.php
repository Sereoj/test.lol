<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

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
