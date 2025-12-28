<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use OpenApi\Attributes as OA;

/**
 * @OA\Schema(
 *     schema="SearchRequest",
 *     type="object",
 *     title="Search Request",
 *     required={"query"},
 *     @OA\Property(
 *         property="query",
 *         type="string",
 *         description="Query (min: 3)",
 *         example="Example query"
 *     ),
 * )
 */
class SearchRequest extends FormRequest
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
            'query' => 'required_without:q|string|min:3',
            'q' => 'required_without:query|string|min:3',
        ];
    }

    public function messages(): array
    {
        return [
            'query.required_without' => 'The query field is required when q is not present.',
            'q.required_without' => 'The q field is required when query is not present.',
        ];
    }

    public function attributes(): array
    {
        return [
            'query' => 'The query field',
            'q' => 'The q field',
        ];
    }

    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json($validator->errors(), 422));
    }
}