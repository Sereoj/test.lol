<?php

namespace App\Http\Requests\Authentication;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class RecoverAccountRequest extends FormRequest
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
     */
    public function rules(): array
    {
        return [
            'token' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
            'password_confirmation' => 'required|string'
        ];
    }

    /**
     * Get custom error messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'token.required' => 'Токен восстановления обязателен',
            'password.required' => 'Новый пароль обязателен',
            'password.min' => 'Пароль должен содержать минимум 8 символов',
            'password.confirmed' => 'Пароли не совпадают',
            'password_confirmation.required' => 'Подтверждение пароля обязательно'
        ];
    }

    /**
     * Define the body parameters for Scribe documentation.
     */
    public function bodyParameters(): array
    {
        return [
            'token' => [
                'description' => 'Токен восстановления, полученный по электронной почте',
                'example' => 'abcdef123456'
            ],
            'password' => [
                'description' => 'Новый пароль, минимум 8 символов',
                'example' => 'newPassword123'
            ],
            'password_confirmation' => [
                'description' => 'Подтверждение нового пароля',
                'example' => 'newPassword123'
            ]
        ];
    }

    /**
     * Handle a failed validation attempt.
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