<?php

namespace App\Services;

use App\Models\Domain;
use App\Models\Setting;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class SettingService
{
    protected ?Domain $domain = null;

    public function forDomain(Domain $domain): static
    {
        $clone = clone $this;
        $clone->domain = $domain;

        return $clone;
    }

    public function domain(): ?Domain
    {
        return $this->domain;
    }

    protected function domainId(): ?int
    {
        return $this->domain?->id ?? resolve_settings_domain_id();
    }

    /** @return array<string, array<string, mixed>> */
    public function groups(): array
    {
        return config('settings.groups', []);
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function fields(): array
    {
        $fields = [];

        foreach ($this->groups() as $groupKey => $group) {
            foreach ($group['fields'] ?? [] as $fieldKey => $definition) {
                $fields[$fieldKey] = $definition + ['group' => $groupKey];
            }
        }

        return $fields;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $rules = [];

        foreach ($this->fields() as $key => $definition) {
            $rules[$key] = $definition['rules'] ?? ['nullable'];
        }

        return $rules;
    }

    public function value(string $key): mixed
    {
        $definition = $this->fields()[$key] ?? [];

        return Setting::get($key, $definition['default'] ?? null, $this->domainId());
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  array<string, UploadedFile|null>  $files
     * @return array{old: array<string, mixed>, new: array<string, mixed>}
     */
    public function save(array $data, array $files = []): array
    {
        $domainId = $this->domainId();

        if ($domainId === null) {
            throw new \RuntimeException('Cannot save settings without a domain.');
        }

        $old = [];
        $new = [];

        foreach ($this->fields() as $key => $definition) {
            $type = $definition['type'] ?? 'text';
            $group = $definition['group'] ?? 'general';

            if ($type !== 'image' && ! array_key_exists($key, $data)) {
                continue;
            }

            $previous = Setting::get($key, $definition['default'] ?? null, $domainId);

            $value = match ($type) {
                'image' => $this->resolveImage($key, $files[$key] ?? null, $data, $domainId),
                'boolean' => array_key_exists($key, $data) && (bool) $data[$key] ? '1' : '0',
                'password' => $this->resolvePassword($key, $data[$key] ?? null, $domainId),
                default => $data[$key] ?? null,
            };

            if ($value === self::skip()) {
                continue;
            }

            if ((string) $previous !== (string) $value) {
                $old[$key] = $type === 'password' ? '[hidden]' : $previous;
                $new[$key] = $type === 'password' ? '[updated]' : $value;
            }

            Setting::set($key, $value, $group, $type, $domainId);
        }

        Setting::flushCache($domainId);

        if ($this->domain) {
            $this->syncDomainProfile($this->domain, $domainId);
        }

        return ['old' => $old, 'new' => $new];
    }

    protected function syncDomainProfile(Domain $domain, int $domainId): void
    {
        $domain->update([
            'website_name' => Setting::get('site_name', $domain->website_name, $domainId),
            'seo_title' => Setting::get('seo_title', $domain->seo_title, $domainId),
            'seo_description' => Setting::get('seo_description', $domain->seo_description, $domainId),
            'seo_keywords' => Setting::get('seo_keywords', $domain->seo_keywords, $domainId),
            'logo_path' => Setting::get('site_logo', $domain->logo_path, $domainId),
            'favicon_path' => Setting::get('site_favicon', $domain->favicon_path, $domainId),
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function resolveImage(string $key, ?UploadedFile $file, array $data, int $domainId): mixed
    {
        $existing = Setting::get($key, null, $domainId);

        if ($file instanceof UploadedFile) {
            $this->deleteFile($existing);

            return $file->store('settings/'.$domainId, 'public');
        }

        if (! empty($data['remove_'.$key])) {
            $this->deleteFile($existing);

            return null;
        }

        return self::skip();
    }

    protected function resolvePassword(string $key, ?string $value, int $domainId): mixed
    {
        return blank($value) ? self::skip() : $value;
    }

    protected function deleteFile(?string $path): void
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }

    protected static function skip(): string
    {
        return '__setting_unchanged__';
    }
}
