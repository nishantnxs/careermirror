<?php

namespace App\Http\Requests\Admin;

use App\Models\Admin;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UpdateModeratorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('admin')->user()?->hasPermission('moderators.manage') ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        /** @var Admin $moderator */
        $moderator = $this->route('moderator');

        return [
            'name' => ['required', 'string', 'max:120'],
            'email' => [
                'required', 'email', 'max:191',
                Rule::unique('admins', 'email')->ignore($moderator->id),
            ],
            'phone' => ['nullable', 'string', 'max:30'],
            'password' => ['nullable', 'confirmed', Password::defaults()],
            'admin_role_id' => [
                'required',
                'integer',
                Rule::exists('admin_roles', 'id'),
            ],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function moderatorData(): array
    {
        $data = [
            'name' => $this->string('name')->trim()->value(),
            'email' => $this->string('email')->trim()->lower()->value(),
            'phone' => $this->filled('phone') ? $this->string('phone')->trim()->value() : null,
            'admin_role_id' => (int) $this->input('admin_role_id'),
            'is_active' => $this->boolean('is_active'),
            'is_super_admin' => false,
        ];

        if ($this->filled('password')) {
            $data['password'] = $this->input('password');
        }

        return $data;
    }
}
