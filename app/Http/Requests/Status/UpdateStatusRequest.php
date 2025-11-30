<?php

namespace App\Http\Requests\Status;

use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Attributes as OA;

/**
 * @OA\Schema(
 *     schema="UpdateStatusRequest",
 *     type="object",
 *     title="UpdateStatus Request",
 *     required={"name", "name.ru", "name.en", "emoji"},
 *     @OA\Property(
 *         property="name",
 *         type="array",
 *         description="Name",
 *         @OA\Items(type="string")
 *     ),
 *     @OA\Property(
 *         property="name.ru",
 *         type="string",
 *         description="Name.ru (max: 256)",
 *         example="Example name.ru"
 *     ),
 *     @OA\Property(
 *         property="name.en",
 *         type="string",
 *         description="Name.en (max: 256)",
 *         example="Example name.en"
 *     ),
 *     @OA\Property(
 *         property="emoji",
 *         type="string",
 *         description="Emoji (max: 10)",
 *         example="Example emoji"
 *     ),
 * )
 */
class UpdateStatusRequest extends FormRequest
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
            'name' => 'sometimes|required|array',
            'name.ru' => 'sometimes|required|string|max:256',
            'name.en' => 'sometimes|required|string|max:256',
            'emoji' => 'sometimes|required|string|max:10',
        ];
    }
}
