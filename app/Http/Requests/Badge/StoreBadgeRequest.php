<?php

namespace App\Http\Requests\Badge;

use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Attributes as OA;

/**
 * @OA\Schema(
 *     schema="StoreBadgeRequest",
 *     type="object",
 *     title="StoreBadge Request",
 *     required={"name", "name.ru", "name.en", "color", "description", "description.ru", "description.en", "options", "options.availability", "options.requirements", "options.requirements.type", "options.requirements.value", "options.price", "image"},
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
 *         property="color",
 *         type="string",
 *         description="Color",
 *         example="Example color"
 *     ),
 *     @OA\Property(
 *         property="description",
 *         type="array",
 *         description="Description",
 *         @OA\Items(type="string")
 *     ),
 *     @OA\Property(
 *         property="description.ru",
 *         type="string",
 *         description="Description.ru (max: 1000)",
 *         example="Example description.ru"
 *     ),
 *     @OA\Property(
 *         property="description.en",
 *         type="string",
 *         description="Description.en (max: 1000)",
 *         example="Example description.en"
 *     ),
 *     @OA\Property(
 *         property="options",
 *         type="array",
 *         description="Options",
 *         @OA\Items(type="string")
 *     ),
 *     @OA\Property(
 *         property="options.availability",
 *         type="string",
 *         description="Options.availability",
 *         example="Example options.availability"
 *     ),
 *     @OA\Property(
 *         property="options.requirements",
 *         type="array",
 *         description="Options.requirements",
 *         @OA\Items(type="string")
 *     ),
 *     @OA\Property(
 *         property="options.requirements.type",
 *         type="string",
 *         description="Options.requirements.type",
 *         example="Example options.requirements.type"
 *     ),
 *     @OA\Property(
 *         property="options.requirements.value",
 *         type="integer",
 *         description="Options.requirements.value",
 *         example=1
 *     ),
 *     @OA\Property(
 *         property="options.price",
 *         type="integer",
 *         description="Options.price",
 *         example=1
 *     ),
 *     @OA\Property(
 *         property="image",
 *         type="string",
 *         description="Image",
 *         example="Example image"
 *     ),
 * )
 */
class StoreBadgeRequest extends FormRequest
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
            'name' => 'required|array',
            'name.ru' => 'required|string|max:256',
            'name.en' => 'required|string|max:256',
            'color' => 'required|string',
            'description' => 'required|array',
            'description.ru' => 'required|string|max:1000',
            'description.en' => 'required|string|max:1000',
            'options' => 'required|array',
            'options.availability' => 'required|string|in:achievement,open,purchase',
            'options.requirements' => 'required_if:options.availability,achievement|array',
            'options.requirements.type' => 'required_if:options.availability,achievement|string|in:comments,uploads,bookmarks,contest_win,likes',
            'options.requirements.value' => 'required_if:options.availability,achievement|integer',
            'options.price' => 'required_if:options.availability,purchase|integer',
            'image' => 'required|string',
        ];
    }
}

//|max:256
