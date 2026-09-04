<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class SettingService
{
    /** @return array<string, array<string, mixed>> */
    public function groups(): array
    {
        return config('settings.groups', []);
    }

    /**
     * Every defined field, flattened to key => definition (with its group attached).
     *
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
     * Validation rules built from the settings definition.
     *
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

    /**
     * Current value for a field, falling back to the definition default.
     */
    public function value(string $key): mixed
    {
        $definition = $this->fields()[$key] ?? [];

        return Setting::get($key, $definition['default'] ?? null);
    }

    /**
     * Persist a validated payload for a single settings group.
     *
     * @param  array<string, mixed>  $data
     * @param  array<string, UploadedFile|null>  $files
     */
    public function save(array $data, array $files = []): void
    {
        foreach ($this->fields() as $key => $definition) {
            $type = $definition['type'] ?? 'text';
            $group = $definition['group'] ?? 'general';

            // Only touch fields that were part of the submitted form.
            if ($type !== 'image' && ! array_key_exists($key, $data)) {
                continue;
            }

            $value = match ($type) {
                'image' => $this->resolveImage($key, $files[$key] ?? null, $data),
                'boolean' => array_key_exists($key, $data) && (bool) $data[$key] ? '1' : '0',
                'password' => $this->resolvePassword($key, $data[$key] ?? null),
                default => $data[$key] ?? null,
            };

            if ($value === self::skip()) {
                continue;
            }

            Setting::set($key, $value, $group, $type);
        }

        Setting::flushCache();
    }

    /**
     * Store a newly uploaded image, honour the "remove" checkbox, or keep the
     * existing file when nothing was submitted.
     *
     * @param  array<string, mixed>  $data
     */
    protected function resolveImage(string $key, ?UploadedFile $file, array $data): mixed
    {
        $existing = Setting::get($key);

        if ($file instanceof UploadedFile) {
            $this->deleteFile($existing);

            return $file->store('settings', 'public');
        }

        if (! empty($data['remove_'.$key])) {
            $this->deleteFile($existing);

            return null;
        }

        return self::skip();
    }

    protected function resolvePassword(string $key, ?string $value): mixed
    {
        return blank($value) ? self::skip() : $value;
    }

    protected function deleteFile(?string $path): void
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }

    /** Sentinel meaning "leave this setting untouched". */
    protected static function skip(): string
    {
        return '__setting_unchanged__';
    }
}
