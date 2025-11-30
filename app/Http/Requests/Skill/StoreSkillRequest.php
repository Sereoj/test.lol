<?php

namespace App\Http\Requests\Skill;

use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Attributes as OA;

/**
 * @OA\Schema(
 *     schema="StoreSkillRequest",
 *     type="object",
 *     title="StoreSkill Request",
 *     required={"name", "name.ru", "name.en", "color"},
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
 *         property="color",
 *         type="string",
 *         description="Color",
 *         example="Example color"
 *     ),
 * )
 */
class StoreSkillRequest extends FormRequest
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
            'color' => 'required|string',
        ];
    }
}
