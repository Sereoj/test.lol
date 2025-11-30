<?php

namespace App\Http\Requests\Source;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use OpenApi\Attributes as OA;

/**
 * @OA\Schema(
 *     schema="UpdateSourceRequest",
 *     type="object",
 *     title="UpdateSource Request",
 *     required={},
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
 *         property="iconUrl",
 *         type="string",
 *         description="IconUrl (max: 255)",
 *         example="Example iconUrl"
 *     ),
 * )
 */
class UpdateSourceRequest extends FormRequest
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
            'name' => 'sometimes|array',
            'name.en' => 'sometimes|string|max:255',
            'name.ru' => 'sometimes|string|max:255',
            'iconUrl' => 'sometimes|string|max:255',
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
