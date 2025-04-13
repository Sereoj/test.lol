<?php

namespace App\Http\Requests\Post;

use App\Models\Posts\Post;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class UpdatePostRequest extends FormRequest
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
            'title' => 'nullable|string|min:3|max:100',
            'content' => 'nullable|string|max:3000',
            'status' => [
                'nullable',
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
        $this->merge([
            'is_free' => $this->boolean('is_free'),
            'is_adult_content' => $this->boolean('is_adult_content'),
            'is_nsfl_content' => $this->boolean('is_nsfl_content'),
            'has_copyright' => $this->boolean('has_copyright'),
        ]);
    }

    protected function failedValidation(Validator $validator)
    {
        $errors = $validator->errors();

        throw new HttpResponseException(
            response()->json(['errors' => $errors], 422)
        );
    }
}
