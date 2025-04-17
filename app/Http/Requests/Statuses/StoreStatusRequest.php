<?php

namespace App\Http\Requests\Statuses;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;   

class StoreStatusRequest extends FormRequest
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
            'name' => 'required|array',
            'name.ru' => 'required|string|max:256',
            'name.en' => 'required|string|max:256',
            'emoji' => 'required|string|max:10',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'The name field is required.',
            'name.ru.required' => 'The name field is required.',
            'name.en.required' => 'The name field is required.',
            'emoji.required' => 'The emoji field is required.',
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'The name field',
            'emoji' => 'The emoji field',
        ];
    }   

    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json($validator->errors(), 422));
    }
}
