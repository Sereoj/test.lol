<?php

namespace App\Http\Requests\Comment;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

/**
 * @OA\Schema(
 *     schema="StoreCommentRequest",
 *     type="object",
 *     title="StoreComment Request",
 *     required={"content"},
 *     @OA\Property(
 *         property="content",
 *         type="string",
 *         description="Content (max: 5000)",
 *         example="Example content"
 *     ),
 *     @OA\Property(
 *         property="parent_id",
 *         type="string",
 *         nullable=true,
 *         description="Parent id",
 *         example="Example parent id"
 *     ),
 * )
 */
class StoreCommentRequest extends FormRequest
{
    /**
     * Определяет, авторизован ли пользователь для выполнения запроса.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Правила валидации, применяемые к запросу.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'content' => 'required|string|max:5000',
            'parent_id' => 'nullable|exists:comments,id',
        ];
    }

    /**
     * Сообщения об ошибках для правил валидации.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'content.required' => 'Текст комментария обязателен.',
            'content.string' => 'Текст комментария должен быть строкой.',
            'content.max' => 'Текст комментария не должен превышать 5000 символов.',
            'parent_id.exists' => 'Родительский комментарий не найден.',
        ];
    }

    /**
     * Обработка неудачной валидации.
     *
     * @param  Validator  $validator
     * @return void
     *
     * @throws HttpResponseException
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => 'Ошибка валидации',
                'errors' => $validator->errors()
            ], 422)
        );
    }
} 