<?php

namespace App\Http\Requests\Challenge;

use Illuminate\Foundation\Http\FormRequest;

class SelectWinnersRequest extends FormRequest
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
            'winners' => 'required|array|min:1',
            'winners.*.place' => 'required|integer|min:1',
            'winners.*.post_id' => 'required|integer|exists:posts,id',
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
            'winners.required' => 'Необходимо указать победителей',
            'winners.array' => 'Победители должны быть массивом',
            'winners.min' => 'Должен быть указан хотя бы один победитель',
            'winners.*.place.required' => 'Место обязательно для каждого победителя',
            'winners.*.place.integer' => 'Место должно быть целым числом',
            'winners.*.place.min' => 'Место должно быть не менее 1',
            'winners.*.post_id.required' => 'ID поста обязателен для каждого победителя',
            'winners.*.post_id.integer' => 'ID поста должен быть целым числом',
            'winners.*.post_id.exists' => 'Один из постов не найден',
        ];
    }
}
