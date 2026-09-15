<?php

namespace App\Http\Requests\Candidate;

use Illuminate\Foundation\Http\FormRequest;

class UploadResumeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('candidate')->check();
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:150'],
            'resume_file' => ['required', 'file', 'mimes:pdf,doc,docx', 'max:5120'],
            'is_default' => ['nullable', 'boolean'],
        ];
    }
}
