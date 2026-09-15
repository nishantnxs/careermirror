<?php

namespace App\Http\Requests\Candidate;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBuiltResumeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('candidate')->check();
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $candidateId = $this->user('candidate')->id;

        return [
            'title' => ['required', 'string', 'max:150'],
            'is_default' => ['nullable', 'boolean'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('candidates', 'email')->ignore($candidateId)],
            'phone' => ['nullable', 'string', 'max:20'],
            'headline' => ['nullable', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
            'content' => ['nullable', 'array'],
            'content.summary' => ['nullable', 'string', 'max:5000'],
            'content.other' => ['nullable', 'string', 'max:5000'],
            'content.skills' => ['nullable'],
            'content.achievements' => ['nullable'],
            'content.experience' => ['nullable', 'array'],
            'content.education' => ['nullable', 'array'],
            'content.certifications' => ['nullable', 'array'],
            'content.languages' => ['nullable', 'array'],
            'content.projects' => ['nullable', 'array'],
        ];
    }

    /** @return array{name: string, email: string, phone: ?string, headline: ?string, location: ?string} */
    public function profileData(): array
    {
        return [
            'name' => $this->string('name')->trim()->value(),
            'email' => $this->string('email')->trim()->value(),
            'phone' => $this->filled('phone') ? $this->string('phone')->trim()->value() : null,
            'headline' => $this->filled('headline') ? $this->string('headline')->trim()->value() : null,
            'location' => $this->filled('location') ? $this->string('location')->trim()->value() : null,
        ];
    }
}
