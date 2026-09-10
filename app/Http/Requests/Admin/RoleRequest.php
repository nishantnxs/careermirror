<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('admin')->user()?->hasPermission('roles.manage') ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $roleId = $this->route('role')?->id;

        return [
            'name' => ['required', 'string', 'max:120'],
            'slug' => [
                'nullable', 'string', 'max:140', 'alpha_dash',
                Rule::unique('admin_roles', 'slug')->ignore($roleId),
            ],
            'description' => ['nullable', 'string', 'max:500'],
            'is_active' => ['nullable', 'boolean'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['integer', Rule::exists('admin_permissions', 'id')],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function roleData(): array
    {
        $data = [
            'name' => $this->string('name')->trim()->value(),
            'description' => $this->filled('description') ? $this->string('description')->trim()->value() : null,
            'is_active' => $this->boolean('is_active'),
        ];

        if ($this->filled('slug')) {
            $data['slug'] = $this->string('slug')->trim()->value();
        }

        return $data;
    }

    /** @return list<int> */
    public function permissionIds(): array
    {
        return collect($this->input('permissions', []))
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->values()
            ->all();
    }
}
