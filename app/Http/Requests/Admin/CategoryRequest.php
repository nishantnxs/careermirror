<?php

namespace App\Http\Requests\Admin;

use App\Enums\CategoryStatus;
use App\Models\Category;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class CategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('admin')->user()?->hasPermission('categories.manage') ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $categoryId = $this->route('category')?->id;

        return [
            'name' => ['required', 'string', 'max:120'],
            'slug' => [
                'nullable',
                'string',
                'max:150',
                'alpha_dash',
                Rule::unique('categories', 'slug')->ignore($categoryId),
            ],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $name = trim((string) $this->input('name'));

            if ($name === '') {
                return;
            }

            $categoryId = $this->route('category')?->id;

            $exists = Category::query()
                ->matchingName($name)
                ->when($categoryId, fn ($query) => $query->whereKeyNot($categoryId))
                ->exists();

            if ($exists) {
                $validator->errors()->add('name', 'A category with this name already exists.');
            }
        });
    }

    /**
     * @return array<string, mixed>
     */
    public function categoryData(): array
    {
        $name = $this->string('name')->trim()->value();
        $categoryId = $this->route('category')?->id;

        $slug = $this->filled('slug')
            ? slug_from($this->string('slug')->trim()->value(), 'category')
            : Category::uniqueSlug($name, $categoryId);

        return [
            'name' => $name,
            'slug' => $slug,
            'sort_order' => (int) ($this->input('sort_order') ?? 0),
            'is_active' => $this->boolean('is_active'),
            'status' => CategoryStatus::Approved,
        ];
    }
}
