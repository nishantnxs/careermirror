<?php

namespace App\Http\Requests\Admin;

use App\Enums\AccountStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class StoreEmployerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'company_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:employers,email'],
            'phone' => ['nullable', 'string', 'max:20'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'status' => ['required', Rule::enum(AccountStatus::class)],
            'domain_ids' => ['required', 'array', 'min:1'],
            'domain_ids.*' => ['integer', 'exists:domains,id'],
        ];
    }

    /** @return array<string, mixed> */
    public function employerData(): array
    {
        return $this->safe()->only([
            'name',
            'company_name',
            'email',
            'phone',
            'password',
            'status',
        ]);
    }
}
