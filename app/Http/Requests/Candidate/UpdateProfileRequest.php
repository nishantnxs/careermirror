<?php

namespace App\Http\Requests\Candidate;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('candidates', 'email')->ignore($candidateId)],
            'phone' => ['nullable', 'string', 'max:20'],
            'headline' => ['nullable', 'string', 'max:255'],
            'summary' => ['nullable', 'string', 'max:5000'],
            'location' => ['nullable', 'string', 'max:255'],
            'current_title' => ['nullable', 'string', 'max:255'],
            'years_of_experience' => ['nullable', 'integer', 'min:0', 'max:60'],
            'website' => ['nullable', 'url', 'max:255'],
            'linkedin_url' => ['nullable', 'url', 'max:255'],
            'date_of_birth' => ['nullable', 'date', 'before:today'],
            'preferred_job_type' => ['nullable', 'string', 'max:50'],
            'expected_salary_min' => ['nullable', 'numeric', 'min:0'],
            'expected_salary_max' => ['nullable', 'numeric', 'min:0', 'gte:expected_salary_min'],
            'currency' => ['required', 'string', 'size:3'],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'remove_avatar' => ['nullable', 'boolean'],
        ];
    }

    /** @return array<string, mixed> */
    public function profileData(): array
    {
        return $this->safe()->except(['avatar', 'remove_avatar']);
    }
}
