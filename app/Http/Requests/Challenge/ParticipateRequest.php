<?php

namespace App\Http\Requests\Challenge;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="ParticipateRequest",
 *     type="object",
 *     title="Participate Request",
 *     required={},
 *     @OA\Property(
 *         property="submission_data",
 *         type="array",
 *         nullable=true,
 *         description="Submission data",
 *         @OA\Items(type="string")
 *     ),
 * )
 */
class ParticipateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'submission_data' => 'nullable|array',
        ];
    }
} 