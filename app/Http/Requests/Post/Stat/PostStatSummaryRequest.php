<?php

namespace App\Http\Requests\Post\Stat;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

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
