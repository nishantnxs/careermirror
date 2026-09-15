<?php

namespace App\Http\Requests\Employer;

use App\Enums\JobStatus;
use App\Models\Category;
use App\Models\Domain;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class JobRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('employer')->check();
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:191'],
            'description' => ['required', 'string', 'max:10000'],
            'location' => ['nullable', 'string', 'max:191'],
            'job_type' => ['nullable', 'string', 'max:100'],
            'experience_level' => ['nullable', 'string', 'max:100'],
            'salary_min' => ['nullable', 'numeric', 'min:0', 'max:999999999.99'],
            'salary_max' => ['nullable', 'numeric', 'min:0', 'max:999999999.99', 'gte:salary_min'],
            'currency' => ['required', 'string', Rule::in(['INR', 'USD', 'EUR', 'GBP'])],
            'status' => ['required', Rule::enum(JobStatus::class)],
            'category_selection' => ['required', 'string', Rule::in(['listed', 'other'])],
            'category_id' => [
                'nullable',
                'integer',
                Rule::exists('categories', 'id')->where(fn ($query) => $query
                    ->where('status', 'approved')
                    ->where('is_active', true)),
            ],
            'custom_category_name' => ['nullable', 'string', 'max:120'],
            'domain_ids' => ['required', 'array', 'min:1'],
            'domain_ids.*' => ['integer'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($this->input('category_selection') === 'listed' && blank($this->input('category_id'))) {
                $validator->errors()->add('category_id', 'Please select a category.');
            }

            if ($this->input('category_selection') === 'other' && blank(trim((string) $this->input('custom_category_name')))) {
                $validator->errors()->add('custom_category_name', 'Please enter a category name.');
            }

            if ($this->input('category_selection') === 'other') {
                $name = trim((string) $this->input('custom_category_name'));

                if ($name !== '' && Category::query()->approved()->matchingName($name)->exists()) {
                    $validator->errors()->add(
                        'custom_category_name',
                        'This category already exists. Please select it from the list.',
                    );
                }
            }

            $allowed = Domain::query()->active()->pluck('id')->map(fn ($id) => (int) $id)->all();
            $selected = collect($this->input('domain_ids', []))->map(fn ($id) => (int) $id)->filter()->unique()->all();

            if ($selected === []) {
                $validator->errors()->add('domain_ids', 'Select at least one domain for this job.');
            }

            foreach ($selected as $domainId) {
                if (! in_array($domainId, $allowed, true)) {
                    $validator->errors()->add('domain_ids', 'Select at least one active website for this job.');
                    break;
                }
            }
        });
    }

    /**
     * @return array<string, mixed>
     */
    public function jobData(): array
    {
        $isOther = $this->input('category_selection') === 'other';

        return [
            'title' => $this->string('title')->trim()->value(),
            'description' => $this->string('description')->trim()->value(),
            'location' => $this->filled('location') ? $this->string('location')->trim()->value() : null,
            'job_type' => $this->filled('job_type') ? $this->string('job_type')->trim()->value() : null,
            'experience_level' => $this->filled('experience_level')
                ? $this->string('experience_level')->trim()->value()
                : null,
            'salary_min' => $this->filled('salary_min') ? $this->input('salary_min') : null,
            'salary_max' => $this->filled('salary_max') ? $this->input('salary_max') : null,
            'currency' => $this->input('currency'),
            'status' => $this->input('status'),
            'category_id' => $isOther ? null : ($this->filled('category_id') ? (int) $this->input('category_id') : null),
            'custom_category_name' => $isOther
                ? $this->string('custom_category_name')->trim()->value()
                : null,
            'domain_ids' => collect($this->input('domain_ids', []))
                ->map(fn ($id) => (int) $id)
                ->filter()
                ->unique()
                ->values()
                ->all(),
        ];
    }
}
