<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    protected function prepareForValidation(): void
    {
        $body = $this->input('body');

        if (! is_string($body)) {
            return;
        }

        $body = str_replace(["\r\n", "\r"], "\n", $body);
        $body = strip_tags($body);
        $body = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $body) ?? '';
        $body = preg_replace('/[^\S\n]+/u', ' ', $body) ?? '';

        $this->merge(['body' => trim($body)]);
    }

    public function rules(): array
    {
        return [
            'body' => ['bail', 'required', 'string', 'max:2000', 'regex:/\S/u'],
        ];
    }

    public function messages(): array
    {
        return [
            'body.required' => 'Please write a message first.',
            'body.max' => 'Messages may not be longer than 2,000 characters.',
            'body.regex' => 'A message cannot be empty.',
        ];
    }
}
