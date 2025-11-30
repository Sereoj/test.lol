<?php

namespace App\Http\Requests\Media;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use OpenApi\Attributes as OA;

/**
 * @OA\Schema(
 *     schema="MediaRequest",
 *     type="object",
 *     title="Media Request",
 *     required={},
 *     @OA\Property(
 *         property="file.*",
 *         type="string",
 *         description="File.* (max: 20480)",
 *         example="Example file.*"
 *     ),
 *     @OA\Property(
 *         property="is_adult",
 *         type="boolean",
 *         nullable=true,
 *         description="Is adult",
 *         example=true
 *     ),
 *     @OA\Property(
 *         property="is_subscription",
 *         type="boolean",
 *         nullable=true,
 *         description="Is subscription",
 *         example=true
 *     ),
 *     @OA\Property(
 *         property="is_paid",
 *         type="boolean",
 *         nullable=true,
 *         description="Is paid",
 *         example=true
 *     ),
 *     @OA\Property(
 *         property="is_author",
 *         type="boolean",
 *         nullable=true,
 *         description="Is author",
 *         example=true
 *     ),
 * )
 */
class MediaRequest extends FormRequest
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
    public function rules()
    {
        return [
            'file.*' => 'file|mimes:jpeg,jpg,png,gif,webp,mp4,avi,mov|max:20480',
            'is_adult' => 'nullable|boolean',
            'is_subscription' => 'nullable|boolean',
            'is_paid' => 'nullable|boolean',
            'is_author' => 'nullable|boolean',
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'is_adult' => filter_var($this->is_adult, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE),
            'is_subscription' => filter_var($this->is_subscription, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE),
            'is_paid' => filter_var($this->is_paid, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE),
            'is_author' => filter_var($this->is_author, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE),
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
