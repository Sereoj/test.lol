<?php

namespace App\Http\Requests\Post;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use OpenApi\Attributes as OA;

/**
 * @OA\Schema(
 *     schema="ReportPostRequest",
 *     type="object",
 *     title="ReportPost Request",
 *     required={"category"},
 *     @OA\Property(
 *         property="category",
 *         type="string",
 *         enum={"spam", "harassment", "hate_speech", "violence", "nsfw", "copyright", "misinformation", "illegal", "ai_generated", "other"},
 *         description="Report category",
 *         example="spam"
 *     ),
 *     @OA\Property(
 *         property="reason",
 *         type="string",
 *         description="Reason (max: 600)",
 *         example="Спам или мошенничество"
 *     ),
 * )
 */
class ReportPostRequest extends FormRequest
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
            'category' => 'required|string|in:spam,harassment,hate_speech,violence,nsfw,copyright,misinformation,illegal,ai_generated,other',
            'reason' => 'required|string|max:600',
        ];
    }

    public function messages(): array
    {
        return [
            'category.required' => 'Категория жалобы обязательна.',
            'category.in' => 'Недопустимая категория жалобы.',
            'reason.required' => 'Причина жалобы обязательна.',
            'reason.string' => 'Причина должна быть строкой.',
            'reason.max' => 'Причина не может быть больше 600 символов.',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        $errors = $validator->errors();

        throw new HttpResponseException(
            response()->json(['errors' => $errors], 422)
        );
    }
}
