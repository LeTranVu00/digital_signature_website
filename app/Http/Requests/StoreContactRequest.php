<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreContactRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => $this->cleanText('name'),
            'email' => strtolower($this->cleanText('email')),
            'phone' => $this->cleanText('phone'),
            'message' => $this->cleanText('message'),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'message' => ['required', 'string', 'max:5000'],
        ];
    }

    private function cleanText(string $key): ?string
    {
        $value = $this->input($key);

        if ($value === null) {
            return null;
        }

        return trim(strip_tags((string) $value));
    }
}
