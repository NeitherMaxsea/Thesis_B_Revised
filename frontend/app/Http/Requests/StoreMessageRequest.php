<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

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
            'body' => ['nullable', 'string', 'max:2000'],
            'attachment' => [
                'nullable',
                'file',
                'mimetypes:image/jpeg,image/png,image/webp,image/gif,video/mp4,video/webm,video/quicktime',
                'max:25600',
            ],
        ];
    }

    public function after(): array
    {
        return [function (Validator $validator): void {
            if (blank($this->input('body')) && ! $this->hasFile('attachment')) {
                $validator->errors()->add('body', 'Please write a message or attach an image or video.');
            }
        }];
    }

    public function messages(): array
    {
        return [
            'body.max' => 'Messages may not be longer than 2,000 characters.',
            'attachment.mimetypes' => 'Attachments must be JPG, PNG, WEBP, GIF, MP4, WEBM, or MOV files.',
            'attachment.max' => 'Attachments may not be larger than 25 MB.',
        ];
    }
}
