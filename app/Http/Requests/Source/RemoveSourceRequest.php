<?php

namespace App\Http\Requests\Source;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use OpenApi\Attributes as OA;

/**
 * @OA\Schema(
 *     schema="RemoveSourceRequest",
 *     type="object",
 *     title="RemoveSource Request",
 *     required={"source_id"},
 *     @OA\Property(
 *         property="source_id",
 *         type="string",
 *         description="Source id",
 *         example="Example source id"
 *     ),
 * )
 */
class RemoveSourceRequest extends FormRequest
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
            'source_id' => 'required|exists:sources,id',
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
