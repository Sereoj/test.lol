<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use OpenApi\Attributes as OA;
use Illuminate\Http\Exceptions\HttpResponseException;   

/**
 * @OA\Schema(
 *     schema="getPostStatisticsRequest",
 *     type="object",
 *     title="getPostStatistics Request",
 *     required={"date_range.start_date", "date_range.end_date"},
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
class getPostStatisticsRequest extends FormRequest
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
            'date_range.start_date' => 'required|date',
            'date_range.end_date' => 'required|date|after_or_equal:date_range.start_date',
        ];
    }

    public function messages()
    {
        return [
            'date_range.start_date.required' => 'The start date is required.',
            'date_range.start_date.date' => 'The start date must be a valid date.',
            'date_range.end_date.required' => 'The end date is required.',
            'date_range.end_date.date' => 'The end date must be a valid date.',
            'date_range.end_date.after_or_equal' => 'The end date must be after or equal to the start date.',
        ];
    }   

    public function attributes(): array
    {
        return [
            'date_range.start_date' => 'The start date',
            'date_range.end_date' => 'The end date',
        ];
    }

    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json($validator->errors(), 422));
    }
}