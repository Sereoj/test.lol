<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Attributes as OA;

/**
 * @OA\Schema(
 *     schema="StoreUserAchievementRequest",
 *     type="object",
 *     title="StoreUserAchievement Request",
 *     required={"achievement_id"},
 *     @OA\Property(
 *         property="achievement_id",
 *         type="string",
 *         description="Achievement id",
 *         example="Example achievement id"
 *     ),
 * )
 */
class StoreUserAchievementRequest extends FormRequest
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
            'achievement_id' => 'required|exists:achievements,id',
        ];
    }
}
