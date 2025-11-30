<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Attributes as OA;

/**
 * @OA\Schema(
 *     schema="UpdateNotificationSettingsRequest",
 *     type="object",
 *     title="UpdateNotificationSettings Request",
 *     required={"email_enabled", "push_enabled", "notify_on_new_message", "notify_on_new_follower", "notify_on_post_like", "notify_on_comment", "notify_on_comment_like", "notify_on_mention"},
 *     @OA\Property(
 *         property="email_enabled",
 *         type="boolean",
 *         description="Email enabled",
 *         example=true
 *     ),
 *     @OA\Property(
 *         property="push_enabled",
 *         type="boolean",
 *         description="Push enabled",
 *         example=true
 *     ),
 *     @OA\Property(
 *         property="notify_on_new_message",
 *         type="boolean",
 *         description="Notify on new message",
 *         example=true
 *     ),
 *     @OA\Property(
 *         property="notify_on_new_follower",
 *         type="boolean",
 *         description="Notify on new follower",
 *         example=true
 *     ),
 *     @OA\Property(
 *         property="notify_on_post_like",
 *         type="boolean",
 *         description="Notify on post like",
 *         example=true
 *     ),
 *     @OA\Property(
 *         property="notify_on_comment",
 *         type="boolean",
 *         description="Notify on comment",
 *         example=true
 *     ),
 *     @OA\Property(
 *         property="notify_on_comment_like",
 *         type="boolean",
 *         description="Notify on comment like",
 *         example=true
 *     ),
 *     @OA\Property(
 *         property="notify_on_mention",
 *         type="boolean",
 *         description="Notify on mention",
 *         example=true
 *     ),
 * )
 */
class UpdateNotificationSettingsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email_enabled' => 'required|boolean',
            'push_enabled' => 'required|boolean',
            'notify_on_new_message' => 'required|boolean',
            'notify_on_new_follower' => 'required|boolean',
            'notify_on_post_like' => 'required|boolean',
            'notify_on_comment' => 'required|boolean',
            'notify_on_comment_like' => 'required|boolean',
            'notify_on_mention' => 'required|boolean',
        ];
    }
}
