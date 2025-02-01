<?php

namespace App\Http\Requests\Post;

use App\Models\Post;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePostRequest extends FormRequest
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
            'title' => 'required|string|min:3|max:255',
            'content' => 'nullable|string|max:10000',
            'status' => [
                'required',
                Rule::in([
                    Post::STATUS_DRAFT,
                    Post::STATUS_PUBLISHED,
                    Post::STATUS_ARCHIVED,
                    Post::STATUS_REJECTED,
                ]),
            ],
            'is_adult_content' => 'boolean',
            'is_nsfl_content' => 'boolean',
            'has_copyright' => 'boolean',
            'price' => 'nullable|numeric|min:0|required_if:is_free,false',
            'is_free' => 'boolean',
            'category_id' => 'nullable|exists:categories,id',
            'settings' => 'nullable',
            'tags_id' => 'array',
            'tags_id.*' => 'exists:tags,id',
            'apps_id' => 'array',
            'apps_id.*' => 'exists:apps,id',
            'media' => 'array',
            'media.*' => 'exists:media,id',
        ];
    }

    protected function prepareForValidation()
    {
        if ($this->has('is_free')) {
            $this->merge([
                'is_free' => $this->boolean('is_free'),
            ]);
        }

        if ($this->has('is_nsfl_content')) {
            $this->merge([
                'is_nsfl_content' => $this->boolean('is_nsfl_content'),
            ]);
        }

        if ($this->has('is_adult_content')) {
            $this->merge([
                'is_adult_content' => $this->boolean('is_adult_content'),
            ]);
        }

        if ($this->has('has_copyright')) {
            $this->merge([
                'has_copyright' => $this->boolean('has_copyright'),
            ]);
        }
    }
}
