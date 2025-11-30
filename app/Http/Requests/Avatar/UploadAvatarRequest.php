<?php

namespace App\Http\Requests\Avatar;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use OpenApi\Attributes as OA;

/**
 * @OA\Schema(
 *     schema="UploadAvatarRequest",
 *     type="object",
 *     title="UploadAvatar Request",
 *     required={"avatar"},
 *     @OA\Property(
 *         property="avatar",
 *         type="string",
 *         description="Avatar (max: 4092)",
 *         example="Example avatar"
 *     ),
 * )
 */
class UploadAvatarRequest extends FormRequest
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
            'avatar' => 'required|file|image|mimes:jpeg,png,jpg,gif|max:4092',
        ];
    }

    public function messages()
    {
        return [
            'avatar.required' => 'The avatar is required.',
            'avatar.image' => 'The avatar must be an image.',
            'avatar.mimes' => 'The avatar must be a file of type: jpeg, png, jpg, gif.',
            'avatar.max' => 'The avatar may not be greater than 4092 kilobytes.',
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
            'avatar' => [
                'description' => 'The avatar image to upload. Must be an image file (jpeg, png, jpg, or gif) with a maximum size of 2MB.',
                'exampleNoAutoGenerate' => true,
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
}
