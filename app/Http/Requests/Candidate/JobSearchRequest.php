<?php

namespace App\Http\Requests\Candidate;

use Illuminate\Foundation\Http\FormRequest;

class JobSearchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('candidate')->check();
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'keyword' => ['nullable', 'string', 'max:150'],
            'location' => ['nullable', 'string', 'max:150'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'job_type' => ['nullable', 'string', 'max:50'],
            'experience_level' => ['nullable', 'string', 'max:50'],
            'salary_min' => ['nullable', 'numeric', 'min:0'],
            'salary_max' => ['nullable', 'numeric', 'min:0'],
            'posted_within' => ['nullable', 'in:1,7,14,30'],
        ];
    }

    /** @return array<string, mixed> */
    public function filters(): array
    {
        return $this->safe()->all();
    }
}
