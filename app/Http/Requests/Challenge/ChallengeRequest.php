<?php

namespace App\Http\Requests\Challenge;

use Illuminate\Foundation\Http\FormRequest;

class ChallengeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'cover_path' => 'nullable|string|max:255',
            'prize_amount' => 'nullable|numeric|min:0',
            'prize_currency' => 'nullable|string|max:5',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'status' => 'nullable|in:draft,active,completed,cancelled',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'title.required' => 'Название челленджа обязательно',
            'title.max' => 'Название челленджа не должно превышать 255 символов',
            'prize_amount.numeric' => 'Призовой фонд должен быть числом',
            'prize_amount.min' => 'Призовой фонд не может быть отрицательным',
            'start_date.date' => 'Дата начала должна быть корректной датой',
            'end_date.date' => 'Дата окончания должна быть корректной датой',
            'end_date.after_or_equal' => 'Дата окончания должна быть после даты начала',
            'status.in' => 'Недопустимый статус челленджа',
        ];
    }
} 