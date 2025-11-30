<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Attributes as OA;

/**
 * @OA\Schema(
 *     schema="StoreUserBadgeRequest",
 *     type="object",
 *     title="StoreUserBadge Request",
 *     required={"badge_id"},
 *     @OA\Property(
 *         property="badge_id",
 *         type="string",
 *         description="Badge id",
 *         example="Example badge id"
 *     ),
 * )
 */
class StoreUserBadgeRequest extends FormRequest
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
            'badge_id' => 'required|exists:badges,id',
        ];
    }
}
