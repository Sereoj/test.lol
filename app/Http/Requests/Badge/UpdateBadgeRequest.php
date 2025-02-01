<?php

namespace App\Http\Requests\Badge;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBadgeRequest extends FormRequest
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
    public function rules()
    {
        return [
            'name' => 'sometimes|required|array',
            'name.ru' => 'sometimes|required|string|max:256',
            'name.en' => 'sometimes|required|string|max:256',
            'color' => 'sometimes|required|string',
            'description' => 'sometimes|required|array',
            'description.ru' => 'sometimes|required|string|max:1000',
            'description.en' => 'sometimes|required|string|max:1000',
            'options' => 'sometimes|required|array',
            'options.availability' => 'sometimes|required|string|in:achievement,open,purchase',
            'options.requirements' => 'required_if:options.availability,achievement|array',
            'options.requirements.type' => 'required_if:options.availability,achievement|string|in:comments,uploads,bookmarks,contest_win,likes',
            'options.requirements.value' => 'required_if:options.availability,achievement|integer',
            'options.price' => 'required_if:options.availability,purchase|integer',
            'image' => 'sometimes|string',
        ];
    }
}
