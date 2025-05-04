<?php

namespace App\Http\Requests\Comment;

use App\Models\Comments\Comment;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class CommentRequest extends FormRequest
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
            'content' => 'required|string|max:3500',
            'parent_id' => [
                'nullable',
                'integer',
                'exists:comments,id',
/*                Rule::notIn([$this->comment_id]),
                function ($attribute, $value, $fail) {
                    if ($value) {
                        $parent = Comment::where('id', $value)
                            ->whereNull('deleted_at')
                            ->first();

                        if (!$parent) {
                            $fail('Родительский комментарий не найден или удален');
                        } elseif ($parent->post_id != $this->post_id) {
                            $fail("Родительский комментарий (ID: {$value}) принадлежит другому посту (ID: {$parent->post_id}). Текущий пост: {$this->post_id}");
                        }
                    }
                }*/
            ]
        ];
    }

    public function messages()
    {
        return [
            'parent_id.not_in' => 'Комментарий не может быть своим же родителем',
            'parent_id.exists' => 'Родительский комментарий не существует'
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
