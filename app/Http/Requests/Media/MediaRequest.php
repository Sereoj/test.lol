<?php

namespace App\Http\Requests\Media;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class MediaRequest extends FormRequest
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
    public function rules()
    {
        return [
            'file.*' => 'file|mimes:jpeg,jpg,png,gif,webp,mp4,avi,mov|max:20480',
            'is_adult' => 'nullable|boolean',
            'is_subscription' => 'nullable|boolean',
            'is_paid' => 'nullable|boolean',
            'is_author' => 'nullable|boolean',
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'is_adult' => filter_var($this->is_adult, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE),
            'is_subscription' => filter_var($this->is_subscription, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE),
            'is_paid' => filter_var($this->is_paid, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE),
            'is_author' => filter_var($this->is_author, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE),
        ]);
    }

    protected function failedValidation(Validator $validator)
    {
        $errors = $validator->errors();

        throw new HttpResponseException(
            response()->json(['errors' => $errors], 422)
        );
    }
}
