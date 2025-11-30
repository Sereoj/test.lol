<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Attributes as OA;

/**
 * @OA\Schema(
 *     schema="StoreLevelRequest",
 *     type="object",
 *     title="StoreLevel Request",
 *     required={"name", "name.ru", "name.en", "experience_required"},
 *     @OA\Property(
 *         property="name",
 *         type="array",
 *         description="Name",
 *         @OA\Items(type="string")
 *     ),
 *     @OA\Property(
 *         property="name.ru",
 *         type="string",
 *         description="Name.ru",
 *         example="Example name.ru"
 *     ),
 *     @OA\Property(
 *         property="name.en",
 *         type="string",
 *         description="Name.en",
 *         example="Example name.en"
 *     ),
 *     @OA\Property(
 *         property="experience_required",
 *         type="integer",
 *         description="Experience required",
 *         example=1
 *     ),
 * )
 */
class StoreLevelRequest extends FormRequest
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
            'name.ru' => 'required|string',
            'name.en' => 'required|string',
            'experience_required' => 'required|integer',
        ];
    }
}
