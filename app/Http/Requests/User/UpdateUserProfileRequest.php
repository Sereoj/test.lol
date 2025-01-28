<?php

namespace App\Http\Requests\User;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateUserProfileRequest extends FormRequest
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
            'username' => 'sometimes|required|string|max:255',
            'seo_meta' => 'sometimes|required|json',
            'slug' => 'sometimes|required|string|max:255|unique:users,slug,' . $this->user()->id,
            'description' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|unique:users,email,' . $this->user()->id,
            'verification' => 'sometimes|required|boolean',
            'experience' => 'sometimes|required|integer',
            'gender' => 'sometimes|required|in:male,female,other',
            'language' => 'sometimes|required|string|max:2',
            'age' => 'sometimes|required|integer',
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
