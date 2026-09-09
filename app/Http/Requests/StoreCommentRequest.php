<?php

namespace App\Http\Requests;

use App\Models\Comment;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreCommentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'content' => trim((string) $this->input('content')),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'content' => [
                'required',
                'string',
                'min:1',
                'max:2000',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if (is_string($value) && $value !== strip_tags($value)) {
                        $fail('Nội dung bình luận không được chứa HTML.');
                    }
                },
            ],
            'parent_id' => ['nullable', 'integer'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $post = $this->route('post');
                $parentId = $this->input('parent_id');

                if (! $post || ! $parentId) {
                    return;
                }

                $parent = Comment::query()
                    ->whereKey($parentId)
                    ->where('post_id', $post->getKey())
                    ->first();

                if (! $parent) {
                    $validator->errors()->add('parent_id', 'Bình luận trả lời không hợp lệ.');

                    return;
                }

                // Replies can target any existing comment in the same post.
            },
        ];
    }
}
