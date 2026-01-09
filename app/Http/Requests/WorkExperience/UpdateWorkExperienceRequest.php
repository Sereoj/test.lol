<?php

namespace App\Http\Requests\WorkExperience;

use Illuminate\Foundation\Http\FormRequest;

class UpdateWorkExperienceRequest extends FormRequest
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
            'company' => ['sometimes', 'required', 'string', 'max:255'],
            'position' => ['sometimes', 'required', 'string', 'max:255'],
            'start_date' => ['sometimes', 'required', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'description' => ['nullable', 'string', 'max:5000'],
            'is_current' => ['boolean'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'company.required' => 'Название компании обязательно',
            'company.max' => 'Название компании не должно превышать 255 символов',
            'position.required' => 'Должность обязательна',
            'position.max' => 'Должность не должна превышать 255 символов',
            'start_date.required' => 'Дата начала обязательна',
            'start_date.date' => 'Дата начала должна быть корректной датой',
            'end_date.date' => 'Дата окончания должна быть корректной датой',
            'end_date.after_or_equal' => 'Дата окончания не может быть раньше даты начала',
            'description.max' => 'Описание не должно превышать 5000 символов',
        ];
    }
}
