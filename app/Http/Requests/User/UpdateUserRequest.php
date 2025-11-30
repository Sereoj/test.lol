<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use OpenApi\Attributes as OA;

/**
 * @OA\Schema(
 *     schema="UpdateUserRequest",
 *     type="object",
 *     title="UpdateUser Request",
 *     required={"name", "email", "password"},
 *     @OA\Property(
 *         property="name",
 *         type="string",
 *         description="Name (max: 255)",
 *         example="Example name"
 *     ),
 *     @OA\Property(
 *         property="email",
 *         type="string",
 *         format="email",
 *         description="Email (max: 255)",
 *         example="user@example.com"
 *     ),
 *     @OA\Property(
 *         property="password",
 *         type="string",
 *         description="Password (min: 8)",
 *         example="Example password"
 *     ),
 * )
 */
class UpdateUserRequest extends FormRequest
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
        $userId = \Auth::id();

        return [
            'name' => 'sometimes|required|string|max:255',
            'email' => [
                'sometimes',
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($userId),
            ],
            'password' => 'sometimes|required|string|min:8',
        ];
    }
}
