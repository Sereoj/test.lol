<?php

namespace App\Http\Requests\Auth;

use App\Rules\NotTempEmail;
use App\Rules\ValidMxRecord;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use OpenApi\Attributes as OA;

/**
 * @OA\Schema(
 *     schema="RegisterRequest",
 *     type="object",
 *     title="Register Request",
 *     required={"username", "email", "password"},
 *     @OA\Property(
 *         property="username",
 *         type="string",
 *         description="Username (max: 255)",
 *         example="Example username"
 *     ),
 *     @OA\Property(
 *         property="email",
 *         type="string",
 *         format="email",
 *         description="Email (min: 3) (max: 255)",
 *         example="user@example.com"
 *     ),
 *     @OA\Property(
 *         property="password",
 *         type="string",
 *         description="Password (min: 8)",
 *         example="Example password"
 *     ),
 *     @OA\Property(
 *         property="remember_me",
 *         type="boolean",
 *         description="Remember me",
 *         example=true
 *     ),
 * )
 */
class RegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'username' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                'regex:/^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}$/',
                'unique:users,email',
                'min:3',
                'max:255',
                new NotTempEmail(),
                new ValidMxRecord()
            ],
            'password' => 'required|string|min:8|confirmed',
            'remember_me' => 'boolean',
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
