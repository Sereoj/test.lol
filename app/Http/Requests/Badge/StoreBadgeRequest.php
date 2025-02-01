<?php

namespace App\Http\Requests\Badge;

use Illuminate\Foundation\Http\FormRequest;

class StoreBadgeRequest extends FormRequest
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
            'name' => 'required|array',
            'name.ru' => 'required|string|max:256',
            'name.en' => 'required|string|max:256',
            'color' => 'required|string',
            'description' => 'required|array',
            'description.ru' => 'required|string|max:1000',
            'description.en' => 'required|string|max:1000',
            'options' => 'required|array',
            'options.availability' => 'required|string|in:achievement,open,purchase',
            'options.requirements' => 'required_if:options.availability,achievement|array',
            'options.requirements.type' => 'required_if:options.availability,achievement|string|in:comments,uploads,bookmarks,contest_win,likes',
            'options.requirements.value' => 'required_if:options.availability,achievement|integer',
            'options.price' => 'required_if:options.availability,purchase|integer',
            'image' => 'required|string',
        ];
    }
}

//|max:256
