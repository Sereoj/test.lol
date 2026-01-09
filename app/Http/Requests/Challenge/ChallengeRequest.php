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
            'type' => 'nullable|in:user,official',
            'winner_selection_method' => 'required|in:manual,voting_public,voting_participants',
            'prize_amount' => 'required|numeric|min:1',
            'prize_currency' => 'sometimes|string|size:3|in:RUB,USD,EUR',
            'prizes' => 'required|array|min:1',
            'prizes.*.place' => 'required|integer|min:1',
            'prizes.*.percentage' => 'required|numeric|min:0|max:100',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'voting_end_date' => 'nullable|date|after:end_date',
            'status' => 'nullable|in:draft,pending_payment,active,voting,selecting_winners,completed,cancelled',
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
            'type.in' => 'Тип челленджа должен быть user или official',
            'winner_selection_method.required' => 'Метод определения победителей обязателен',
            'winner_selection_method.in' => 'Недопустимый метод определения победителей',
            'prize_amount.required' => 'Призовой фонд обязателен',
            'prize_amount.numeric' => 'Призовой фонд должен быть числом',
            'prize_amount.min' => 'Призовой фонд должен быть не менее 1',
            'prize_currency.size' => 'Валюта должна содержать 3 символа',
            'prize_currency.in' => 'Допустимые валюты: RUB, USD, EUR',
            'prizes.required' => 'Необходимо указать призовые места',
            'prizes.array' => 'Призовые места должны быть массивом',
            'prizes.min' => 'Должно быть указано хотя бы одно призовое место',
            'prizes.*.place.required' => 'Место обязательно для каждого приза',
            'prizes.*.place.integer' => 'Место должно быть целым числом',
            'prizes.*.place.min' => 'Место должно быть не менее 1',
            'prizes.*.percentage.required' => 'Процент обязателен для каждого приза',
            'prizes.*.percentage.numeric' => 'Процент должен быть числом',
            'prizes.*.percentage.min' => 'Процент не может быть отрицательным',
            'prizes.*.percentage.max' => 'Процент не может превышать 100',
            'start_date.date' => 'Дата начала должна быть корректной датой',
            'end_date.date' => 'Дата окончания должна быть корректной датой',
            'end_date.after_or_equal' => 'Дата окончания должна быть после даты начала',
            'voting_end_date.date' => 'Дата окончания голосования должна быть корректной датой',
            'voting_end_date.after' => 'Дата окончания голосования должна быть после даты окончания челленджа',
            'status.in' => 'Недопустимый статус челленджа',
        ];
    }
} 