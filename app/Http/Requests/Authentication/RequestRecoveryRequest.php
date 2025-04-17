<?php

namespace App\Http\Requests\Authentication;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class RequestRecoveryRequest extends FormRequest
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
            'email' => 'required|email'
        ];
    }

    /**
     * Get custom error messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'email.required' => 'Email обязателен',
            'email.email' => 'Укажите корректный email адрес'
        ];
    }

    /**
     * Define the body parameters for Scribe documentation.
     */
    public function bodyParameters(): array
    {
        return [
            'email' => [
                'description' => 'Email адрес, связанный с удаленным аккаунтом',
                'example' => 'user@example.com'
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