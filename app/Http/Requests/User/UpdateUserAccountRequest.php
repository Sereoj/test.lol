<?php

namespace App\Http\Requests\User;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Auth;

class UpdateUserAccountRequest extends FormRequest
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
            'current_password' => 'required_with:new_password|string',
            'new_password' => 'sometimes|string|min:8|confirmed',
            'new_password_confirmation' => 'required_with:new_password|string',
            'username' => 'sometimes|string|max:255',
            'slug' => 'sometimes|string|max:255|unique:users,slug,' . Auth::id(),
            'description' => 'sometimes|string|max:255',
            'website' => 'nullable|string|url|max:255',
            'gender' => 'nullable|in:male,female,other',
            'location_id' => 'sometimes|exists:locations,id',
            'language' => 'nullable|string|in:en,ru',
            'age' => 'nullable|integer|min:16|max:100'
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
            'email' => [
                'description' => 'Email пользователя',
                'example' => 'user@example.com',
            ],
            'current_password' => [
                'description' => 'Текущий пароль пользователя',
                'example' => 'current_password123',
            ],
            'new_password' => [
                'description' => 'Новый пароль пользователя',
                'example' => 'new_password123',
            ],
            'new_password_confirmation' => [
                'description' => 'Подтверждение нового пароля',
                'example' => 'new_password123',
            ],
            'username' => [
                'description' => 'Имя пользователя',
                'example' => 'John Doe',
            ],
            'slug' => [
                'description' => 'Уникальный идентификатор пользователя для URL',
                'example' => 'john-doe',
            ],
            'description' => [
                'description' => 'Описание пользователя',
                'example' => 'Разработчик программного обеспечения',
            ],
            'website' => [
                'description' => 'Веб-сайт пользователя',
                'example' => 'https://example.com',
            ],
            'gender' => [
                'description' => 'Пол пользователя (один из: male, female, other)',
                'example' => 'male',
            ],
            'location_id' => [
                'description' => 'ID местоположения пользователя',
                'example' => 1,
            ],
            'language' => [
                'description' => 'Язык пользователя (один из: en, ru)',
                'example' => 'ru',
            ],
            'age' => [
                'description' => 'Возраст пользователя',
                'example' => 25,
            ],
        ];
    }

    /**
     * Handle a failed validation attempt.
     *
     * @param  \Illuminate\Contracts\Validation\Validator  $validator
     * @return void
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422)
        );
    }
}
