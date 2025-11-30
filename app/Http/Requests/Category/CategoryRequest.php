<?php

namespace App\Http\Requests\Category;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use OpenApi\Attributes as OA;

/**
 * @OA\Schema(
 *     schema="CategoryRequest",
 *     type="object",
 *     title="Category Request",
 *     required={"name", "name.en", "name.ru"},
 *     @OA\Property(
 *         property="name",
 *         type="array",
 *         description="Name",
 *         @OA\Items(type="string")
 *     ),
 *     @OA\Property(
 *         property="name.en",
 *         type="string",
 *         description="Name.en (max: 255)",
 *         example="Example name.en"
 *     ),
 *     @OA\Property(
 *         property="name.ru",
 *         type="string",
 *         description="Name.ru (max: 255)",
 *         example="Example name.ru"
 *     ),
 *     @OA\Property(
 *         property="description",
 *         type="string",
 *         nullable=true,
 *         description="Description",
 *         example="Example description"
 *     ),
 * )
 */
class CategoryRequest extends FormRequest
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
            'name.en' => 'required|string|max:255',
            'name.ru' => 'required|string|max:255',
            'description' => 'nullable|string',
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
