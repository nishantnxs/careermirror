<?php

namespace App\Http\Requests\Employer;

use App\Models\JobApplication;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StartConversationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('employer')->check();
    }

    protected function prepareForValidation(): void
    {
        if ($this->filled('job_application_id') && ! $this->filled('candidate_id')) {
            $candidateId = JobApplication::query()
                ->whereKey($this->integer('job_application_id'))
                ->value('candidate_id');

            if ($candidateId) {
                $this->merge(['candidate_id' => $candidateId]);
            }
        }
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $employerId = $this->user('employer')->id;

        return [
            'job_application_id' => [
                'required',
                'integer',
                Rule::exists('job_applications', 'id')->where(function ($query) use ($employerId) {
                    $query->whereIn('job_posting_id', function ($sub) use ($employerId) {
                        $sub->select('id')->from('job_postings')->where('employer_id', $employerId);
                    });
                }),
            ],
            'candidate_id' => ['required', 'integer', 'exists:candidates,id'],
            'subject' => ['nullable', 'string', 'max:191'],
            'body' => ['required', 'string', 'max:5000'],
        ];
    }
}
