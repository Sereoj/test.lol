<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use OpenApi\Attributes as OA;

/**
 * @OA\Schema(
 *     schema="ReportRequest",
 *     type="object",
 *     title="Report Request",
 *     required={"content_type", "content_id", "category"},
 *     @OA\Property(
 *         property="content_type",
 *         type="string",
 *         enum={"post", "comment"},
 *         description="Type of content being reported",
 *         example="post"
 *     ),
 *     @OA\Property(
 *         property="content_id",
 *         type="integer",
 *         description="ID of the content being reported",
 *         example=1
 *     ),
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
class ReportRequest extends FormRequest
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
            'content_type' => 'required|string|in:post,comment',
            'content_id' => 'required|integer|min:1',
            'category' => 'required|string|in:spam,harassment,hate_speech,violence,nsfw,copyright,misinformation,illegal,ai_generated,other',
            'reason' => 'required|string|max:600',
        ];
    }

    public function messages(): array
    {
        return [
            'content_type.required' => 'Тип контента обязателен.',
            'content_type.in' => 'Недопустимый тип контента. Допустимые значения: post, comment.',
            'content_id.required' => 'ID контента обязателен.',
            'content_id.integer' => 'ID контента должен быть числом.',
            'content_id.min' => 'ID контента должен быть больше 0.',
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
