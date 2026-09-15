<?php

namespace App\Http\Requests\Employer;

use App\Enums\ApplicationStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateApplicantStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('employer')->check();
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'status' => [
                'required',
                Rule::enum(ApplicationStatus::class)->except(ApplicationStatus::Withdrawn),
            ],
        ];
    }
}
