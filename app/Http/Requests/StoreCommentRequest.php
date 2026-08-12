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
                        $fail('Noi dung binh luan khong duoc chua HTML.');
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
                    $validator->errors()->add('parent_id', 'Binh luan tra loi khong hop le.');

                    return;
                }

                // Replies can target any existing comment in the same post.
            },
        ];
    }
}
