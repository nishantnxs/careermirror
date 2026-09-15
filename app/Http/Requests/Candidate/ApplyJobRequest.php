<?php

namespace App\Http\Requests\Candidate;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ApplyJobRequest extends FormRequest
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
            'candidate_resume_id' => [
                'required',
                'integer',
                Rule::exists('candidate_resumes', 'id')->where('candidate_id', $candidateId),
            ],
            'cover_letter' => ['nullable', 'string', 'max:5000'],
        ];
    }
}
