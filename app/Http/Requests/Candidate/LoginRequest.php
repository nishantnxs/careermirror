<?php

namespace App\Http\Requests\Candidate;

use App\Http\Requests\Concerns\AuthenticatesAccount;
use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    use AuthenticatesAccount;

    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email', 'max:255'],
            'password' => ['required', 'string'],
            'remember' => ['nullable', 'boolean'],
        ];
    }

    protected function guardName(): string
    {
        return 'candidate';
    }
}
