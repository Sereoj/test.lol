<?php

namespace App\Http\Requests\Auth;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use OpenApi\Attributes as OA;

/**
 * @OA\Schema(
 *     schema="RefreshTokenRequest",
 *     type="object",
 *     title="RefreshToken Request",
 *     required={"refresh_token"},
 *     @OA\Property(
 *         property="refresh_token",
 *         type="string",
 *         description="Refresh token",
 *         example="Example refresh token"
 *     ),
 * )
 */
class RefreshTokenRequest extends FormRequest
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
            'refresh_token' => 'required|string',
        ];
    }

    public function messages(): array
    {
        return [
            'refresh_token.required' => 'The refresh token is required.',
            'refresh_token.string' => 'The refresh token must be a valid string.',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        $errors = $validator->errors();

        throw new HttpResponseException(
            response()->json(['errors' => $errors], 422)
        );
    }
}
