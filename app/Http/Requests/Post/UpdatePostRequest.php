<?php

namespace App\Http\Requests\Post;

use App\Models\Posts\Post;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;
use OpenApi\Attributes as OA;

/**
 * @OA\Schema(
 *     schema="UpdatePostRequest",
 *     type="object",
 *     title="UpdatePost Request",
 *     required={"price"},
 *     @OA\Property(
 *         property="title",
 *         type="string",
 *         nullable=true,
 *         description="Title (min: 3) (max: 100)",
 *         example="Example title"
 *     ),
 *     @OA\Property(
 *         property="content",
 *         type="string",
 *         nullable=true,
 *         description="Content (max: 3000)",
 *         example="Example content"
 *     ),
 *     @OA\Property(
 *         property="status",
 *         type="string",
 *         nullable=true,
 *         description="Status",
 *         example="Example status"
 *     ),
 *     @OA\Property(
 *         property="is_adult_content",
 *         type="boolean",
 *         description="Is adult content",
 *         example=true
 *     ),
 *     @OA\Property(
 *         property="is_nsfl_content",
 *         type="boolean",
 *         description="Is nsfl content",
 *         example=true
 *     ),
 *     @OA\Property(
 *         property="has_copyright",
 *         type="boolean",
 *         description="Has copyright",
 *         example=true
 *     ),
 *     @OA\Property(
 *         property="price",
 *         type="number",
 *         nullable=true,
 *         description="Price (min: 0)",
 *         example=1
 *     ),
 *     @OA\Property(
 *         property="is_free",
 *         type="boolean",
 *         description="Is free",
 *         example=true
 *     ),
 *     @OA\Property(
 *         property="category_id",
 *         type="string",
 *         nullable=true,
 *         description="Category id",
 *         example="Example category id"
 *     ),
 *     @OA\Property(
 *         property="settings",
 *         type="string",
 *         nullable=true,
 *         description="Settings",
 *         example="Example settings"
 *     ),
 *     @OA\Property(
 *         property="tags_id",
 *         type="array",
 *         description="Tags id",
 *         @OA\Items(type="string")
 *     ),
 *     @OA\Property(
 *         property="tags_id.*",
 *         type="string",
 *         description="Tags id.*",
 *         example="Example tags id.*"
 *     ),
 *     @OA\Property(
 *         property="apps_id",
 *         type="array",
 *         description="Apps id",
 *         @OA\Items(type="string")
 *     ),
 *     @OA\Property(
 *         property="apps_id.*",
 *         type="string",
 *         description="Apps id.*",
 *         example="Example apps id.*"
 *     ),
 *     @OA\Property(
 *         property="media",
 *         type="array",
 *         description="Media",
 *         @OA\Items(type="string")
 *     ),
 *     @OA\Property(
 *         property="media.*",
 *         type="string",
 *         description="Media.*",
 *         example="Example media.*"
 *     ),
 *     @OA\Property(
 *         property="collaborator_ids",
 *         type="array",
 *         description="IDs соавторов (max: 5, только взаимные друзья)",
 *         @OA\Items(type="integer")
 *     ),
 *     @OA\Property(
 *         property="collaborator_ids.*",
 *         type="integer",
 *         description="User ID соавтора",
 *         example=42
 *     ),
 * )
 */
class UpdatePostRequest extends FormRequest
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
            'title' => 'nullable|string|min:3|max:100',
            'content' => 'nullable|string|max:3000',
            'status' => [
                'nullable',
                Rule::in([
                    Post::STATUS_DRAFT,
                    Post::STATUS_PUBLISHED,
                    Post::STATUS_ARCHIVED,
                    Post::STATUS_REJECTED,
                ]),
            ],
            'is_adult_content' => 'boolean',
            'is_nsfl_content' => 'boolean',
            'has_copyright' => 'boolean',
            'price' => 'nullable|numeric|min:0|required_if:is_free,false',
            'is_free' => 'boolean',
            'category_id' => 'nullable|exists:categories,id',
            'settings' => 'nullable',
            'tags_id' => 'array',
            'tags_id.*' => 'exists:tags,id',
            'apps_id' => 'array',
            'apps_id.*' => 'exists:apps,id',
            'media' => 'array',
            'media.*' => 'exists:media,id',
            'collaborator_ids' => 'nullable|array|max:5',
            'collaborator_ids.*' => 'exists:users,id',
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'is_free' => $this->boolean('is_free'),
            'is_adult_content' => $this->boolean('is_adult_content'),
            'is_nsfl_content' => $this->boolean('is_nsfl_content'),
            'has_copyright' => $this->boolean('has_copyright'),
        ]);
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if (!$this->has('collaborator_ids') || !is_array($this->collaborator_ids)) {
                return;
            }

            $userId = \Illuminate\Support\Facades\Auth::id();

            // Проверка 1: Автор не может добавить себя
            if (in_array($userId, $this->collaborator_ids)) {
                $validator->errors()->add(
                    'collaborator_ids',
                    'Вы не можете добавить себя в соавторы.'
                );
                return;
            }

            // Проверка 2: Взаимная дружба
            $followService = app(\App\Services\Users\UserFollowService::class);

            foreach ($this->collaborator_ids as $collaboratorId) {
                if (!$followService->areMutualFriends($userId, $collaboratorId)) {
                    $collaborator = \App\Models\Users\User::find($collaboratorId);
                    $username = $collaborator ? $collaborator->username : "ID {$collaboratorId}";
                    $validator->errors()->add(
                        'collaborator_ids',
                        "Пользователь {$username} не является вашим взаимным другом."
                    );
                }
            }
        });
    }

    protected function failedValidation(Validator $validator)
    {
        $errors = $validator->errors();

        throw new HttpResponseException(
            response()->json(['errors' => $errors], 422)
        );
    }
}
