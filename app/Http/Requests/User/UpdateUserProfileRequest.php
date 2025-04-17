<?php

namespace App\Http\Requests\User;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateUserProfileRequest extends FormRequest
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
        $userId = $this->user() ? $this->user()->id : null;

        return [
            'username' => 'sometimes|required|string|max:255',
            'seo_meta' => 'sometimes|required|json',
            'slug' => 'sometimes|required|string|max:255|unique:users,slug,' . ($userId ?? ''),
            'description' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|unique:users,email,' . ($userId ?? ''),
            'verification' => 'sometimes|required|boolean',
            'experience' => 'sometimes|required|integer',
            'gender' => 'sometimes|required|in:male,female,other',
            'language' => 'sometimes|required|string|max:2',
            'age' => 'sometimes|required|integer',
        ];
    }

    /**
     * Define the body parameters for Scribe documentation.
     *
     * @return array<string, array<string, mixed>>
     */
    public function bodyParameters(): array
    {
        return [
            'username' => [
                'description' => 'The username of the user.',
                'example' => 'johndoe',
            ],
            'seo_meta' => [
                'description' => 'SEO metadata in JSON format.',
                'example' => '{"title": "John Doe Profile", "description": "A user profile"}',
            ],
            'slug' => [
                'description' => 'A unique slug for the user profile.',
                'example' => 'john-doe',
            ],
            'description' => [
                'description' => 'A short description of the user.',
                'example' => 'Software developer with 5 years of experience.',
            ],
            'email' => [
                'description' => 'The user\'s email address.',
                'example' => 'john.doe@example.com',
            ],
            'verification' => [
                'description' => 'Whether the user\'s profile is verified.',
                'example' => true,
            ],
            'experience' => [
                'description' => 'Years of experience.',
                'example' => 5,
            ],
            'gender' => [
                'description' => 'The user\'s gender.',
                'example' => 'male',
            ],
            'language' => [
                'description' => 'The user\'s preferred language (2-letter code).',
                'example' => 'en',
            ],
            'age' => [
                'description' => 'The user\'s age.',
                'example' => 30,
            ],
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        $errors = $validator->errors();

        throw new HttpResponseException(
            response()->json(['errors' => $errors], 422)
        );
    }

    public function messages(): array
    {
        return [
            'username.required' => 'The username field is required.',
            'seo_meta.required' => 'The seo_meta field is required.',
            'slug.required' => 'The slug field is required.',
            'description.required' => 'The description field is required.',
            'email.required' => 'The email field is required.',
            'verification.required' => 'The verification field is required.',
            'experience.required' => 'The experience field is required.',
            'gender.required' => 'The gender field is required.',
            'language.required' => 'The language field is required.',
            'age.required' => 'The age field is required.',
        ];
    }       

    public function attributes(): array
    {
        return [
            'username' => 'The username field',
            'seo_meta' => 'The seo_meta field',
            'slug' => 'The slug field',
            'description' => 'The description field',
            'email' => 'The email field',
            'verification' => 'The verification field',
            'experience' => 'The experience field',
            'gender' => 'The gender field',
            'language' => 'The language field',
            'age' => 'The age field',
        ];
    }   

    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json($validator->errors(), 422));
    }
}
