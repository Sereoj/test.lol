<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

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
