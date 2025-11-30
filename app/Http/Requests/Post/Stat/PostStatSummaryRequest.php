<?php

namespace App\Http\Requests\Post\Stat;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use OpenApi\Attributes as OA;

/**
 * @OA\Schema(
 *     schema="PostStatSummaryRequest",
 *     type="object",
 *     title="PostStatSummary Request",
 *     required={"date_range.start_date", "date_range.end_date"},
 *     @OA\Property(
 *         property="category_id",
 *         type="integer",
 *         nullable=true,
 *         description="Category id",
 *         example=1
 *     ),
 *     @OA\Property(
 *         property="date_range",
 *         type="array",
 *         nullable=true,
 *         description="Date range",
 *         @OA\Items(type="string")
 *     ),
 *     @OA\Property(
 *         property="date_range.start_date",
 *         type="string",
 *         format="date",
 *         description="Date range.start date",
 *         example="2024-01-01"
 *     ),
 *     @OA\Property(
 *         property="date_range.end_date",
 *         type="string",
 *         format="date",
 *         description="Date range.end date",
 *         example="2024-01-01"
 *     ),
 * )
 */
class PostStatSummaryRequest extends FormRequest
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
            'category_id' => 'nullable|integer|exists:categories,id',
            'date_range' => 'nullable|array',
            'date_range.start_date' => 'required_with:date_range.end_date|date',
            'date_range.end_date' => 'required_with:date_range.start_date|date|after_or_equal:date_range.start_date',
        ];
    }

    public function messages()
    {
        return [
            'date_range.start_date.date' => 'The start date must be a valid date.',
            'date_range.end_date.date' => 'The end date must be a valid date.',
            'date_range.end_date.after_or_equal' => 'The end date must be after or equal to the start date.',
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
