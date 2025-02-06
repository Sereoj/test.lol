<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

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
}
