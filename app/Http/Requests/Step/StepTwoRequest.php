<?php

namespace App\Http\Requests\Step;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use OpenApi\Attributes as OA;

/**
 * @OA\Schema(
 *     schema="StepTwoRequest",
 *     type="object",
 *     title="StepTwo Request",
 *     required={"skill_ids"},
 *     @OA\Property(
 *         property="skill_ids",
 *         type="array",
 *         description="Skill ids",
 *         @OA\Items(type="string")
 *     ),
 *     @OA\Property(
 *         property="skill_ids.*",
 *         type="string",
 *         description="Skill ids.*",
 *         example="Example skill ids.*"
 *     ),
 * )
 */
class StepTwoRequest extends FormRequest
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
            'skill_ids' => 'required|array',
            'skill_ids.*' => 'exists:skills,id',
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
