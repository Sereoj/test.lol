<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Attributes as OA;

/**
 * @OA\Schema(
 *     schema="UserPersonalizationRequest",
 *     type="object",
 *     title="UserPersonalization Request",
 *     required={},
 *     @OA\Property(
 *         property="badge_id",
 *         type="integer",
 *         nullable=true,
 *         description="Badge id",
 *         example=1
 *     ),
 *     @OA\Property(
 *         property="employment_status_id",
 *         type="integer",
 *         nullable=true,
 *         description="Employment status id",
 *         example=1
 *     ),
 *     @OA\Property(
 *         property="status_id",
 *         type="integer",
 *         nullable=true,
 *         description="Status id",
 *         example=1
 *     ),
 * )
 */
class UserPersonalizationRequest extends FormRequest
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
            'badge_id' => 'nullable|integer|exists:badges,id',
            'employment_status_id' => 'nullable|integer|exists:employment_statuses,id',
            'status_id' => 'nullable|integer|exists:statuses,id',
        ];
    }
}
