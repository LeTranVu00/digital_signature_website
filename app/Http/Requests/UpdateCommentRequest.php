<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCommentRequest extends FormRequest
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
                        $fail('Noi dung binh luan khong duoc chua HTML.');
                    }
                },
            ],
        ];
    }
}
