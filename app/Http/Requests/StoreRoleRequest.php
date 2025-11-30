<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Attributes as OA;

/**
 * @OA\Schema(
 *     schema="StoreRoleRequest",
 *     type="object",
 *     title="StoreRole Request",
 *     required={"name", "name.ru", "name.en", "type"},
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
 *         property="type",
 *         type="string",
 *         description="Type",
 *         example="Example type"
 *     ),
 * )
 */
class StoreRoleRequest extends FormRequest
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
            'type' => 'required|in:admin,user,moderator,guest',
        ];
    }
}
