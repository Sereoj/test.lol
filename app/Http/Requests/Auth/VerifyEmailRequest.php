<?php

namespace App\Http\Requests\Auth;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use OpenApi\Attributes as OA;

/**
 * @OA\Schema(
 *     schema="VerifyEmailRequest",
 *     type="object",
 *     title="VerifyEmail Request",
 *     required={"email", "code"},
 *     @OA\Property(
 *         property="email",
 *         type="string",
 *         format="email",
 *         description="Email",
 *         example="user@example.com"
 *     ),
 *     @OA\Property(
 *         property="code",
 *         type="string",
 *         description="Code",
 *         example="Example code"
 *     ),
 * )
 */
class VerifyEmailRequest extends FormRequest
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
            'email' => 'required|email|exists:users,email',
            'code' => 'required|string|exists:email_verifications,code',
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
