<?php

namespace App\Http\Requests\Admin;

use App\Enums\DomainStatus;
use App\Models\Domain;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DomainRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('admin')->user()?->hasPermission('domains.manage') ?? false;
    }

    protected function prepareForValidation(): void
    {
        if ($this->filled('host')) {
            $this->merge(['host' => Domain::normalizeHost($this->input('host'))]);
        }

        if ($this->filled('url')) {
            $url = rtrim((string) $this->input('url'), '/');
            if (! str_contains($url, '://')) {
                $url = 'https://'.$url;
            }
            $this->merge(['url' => $url]);
        }
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $domainId = $this->route('domain')?->id;

        return [
            'name' => ['required', 'string', 'max:150'],
            'host' => [
                'required',
                'string',
                'max:191',
                Rule::unique('domains', 'host')->ignore($domainId),
            ],
            'url' => ['required', 'url', 'max:255'],
            'website_name' => ['nullable', 'string', 'max:150'],
            'status' => ['required', Rule::enum(DomainStatus::class)],
            'is_default' => ['nullable', 'boolean'],
            'grant_all_employers' => ['nullable', 'boolean'],
            'seo_title' => ['nullable', 'string', 'max:191'],
            'seo_description' => ['nullable', 'string', 'max:1000'],
            'seo_keywords' => ['nullable', 'string', 'max:255'],
            'logo' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp,svg', 'max:2048'],
            'favicon' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp,ico', 'max:1024'],
            'remove_logo' => ['nullable', 'boolean'],
            'remove_favicon' => ['nullable', 'boolean'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function domainData(): array
    {
        return [
            'name' => $this->string('name')->trim()->value(),
            'host' => $this->input('host'),
            'url' => $this->input('url'),
            'website_name' => $this->filled('website_name')
                ? $this->string('website_name')->trim()->value()
                : $this->string('name')->trim()->value(),
            'status' => $this->input('status'),
            'is_default' => $this->boolean('is_default'),
            'seo_title' => $this->filled('seo_title') ? $this->string('seo_title')->trim()->value() : null,
            'seo_description' => $this->filled('seo_description')
                ? $this->string('seo_description')->trim()->value()
                : null,
            'seo_keywords' => $this->filled('seo_keywords')
                ? $this->string('seo_keywords')->trim()->value()
                : null,
        ];
    }
}
