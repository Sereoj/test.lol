<?php

namespace App\Http\Requests\Challenge;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="ChallengeRequest",
 *     type="object",
 *     title="Challenge Request",
 *     required={"title"},
 *     @OA\Property(
 *         property="title",
 *         type="string",
 *         description="Title (max: 255)",
 *         example="Example title"
 *     ),
 *     @OA\Property(
 *         property="description",
 *         type="string",
 *         nullable=true,
 *         description="Description",
 *         example="Example description"
 *     ),
 *     @OA\Property(
 *         property="cover_path",
 *         type="string",
 *         nullable=true,
 *         description="Cover path (max: 255)",
 *         example="Example cover path"
 *     ),
 *     @OA\Property(
 *         property="prize_amount",
 *         type="number",
 *         nullable=true,
 *         description="Prize amount (min: 0)",
 *         example=1
 *     ),
 *     @OA\Property(
 *         property="prize_currency",
 *         type="string",
 *         nullable=true,
 *         description="Prize currency (max: 5)",
 *         example="Example prize currency"
 *     ),
 *     @OA\Property(
 *         property="start_date",
 *         type="string",
 *         format="date",
 *         nullable=true,
 *         description="Start date",
 *         example="2024-01-01"
 *     ),
 *     @OA\Property(
 *         property="end_date",
 *         type="string",
 *         format="date",
 *         nullable=true,
 *         description="End date",
 *         example="2024-01-01"
 *     ),
 *     @OA\Property(
 *         property="status",
 *         type="string",
 *         nullable=true,
 *         description="Status",
 *         example="Example status"
 *     ),
 * )
 */
class ChallengeRequest extends FormRequest
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
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'cover_path' => 'nullable|string|max:255',
            'prize_amount' => 'nullable|numeric|min:0',
            'prize_currency' => 'nullable|string|max:5',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'status' => 'nullable|in:draft,active,completed,cancelled',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'title.required' => 'Название челленджа обязательно',
            'title.max' => 'Название челленджа не должно превышать 255 символов',
            'prize_amount.numeric' => 'Призовой фонд должен быть числом',
            'prize_amount.min' => 'Призовой фонд не может быть отрицательным',
            'start_date.date' => 'Дата начала должна быть корректной датой',
            'end_date.date' => 'Дата окончания должна быть корректной датой',
            'end_date.after_or_equal' => 'Дата окончания должна быть после даты начала',
            'status.in' => 'Недопустимый статус челленджа',
        ];
    }
} 