<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class Setting extends Model
{
    protected $fillable = [
        'domain_id',
        'key',
        'value',
        'group',
        'type',
    ];

    protected static function booted(): void
    {
        static::saved(function (self $setting): void {
            static::flushCache($setting->domain_id);
        });

        static::deleted(function (self $setting): void {
            static::flushCache($setting->domain_id);
        });
    }

    public function domain(): BelongsTo
    {
        return $this->belongsTo(Domain::class);
    }

    public static function cacheKey(?int $domainId): string
    {
        return 'settings.domain.'.($domainId ?? 'none');
    }

    /**
     * @return array<string, string|null>
     */
    public static function allSettings(?int $domainId = null): array
    {
        $domainId ??= resolve_settings_domain_id();

        if ($domainId === null) {
            return [];
        }

        return Cache::rememberForever(
            self::cacheKey($domainId),
            fn () => static::query()
                ->where('domain_id', $domainId)
                ->pluck('value', 'key')
                ->all()
        );
    }

    public static function get(string $key, mixed $default = null, ?int $domainId = null): mixed
    {
        $value = static::allSettings($domainId)[$key] ?? null;

        return ($value === null || $value === '') ? $default : $value;
    }

    public static function set(
        string $key,
        mixed $value,
        string $group = 'general',
        string $type = 'text',
        ?int $domainId = null,
    ): void {
        $domainId ??= resolve_settings_domain_id();

        if ($domainId === null) {
            throw new \RuntimeException('Cannot save a setting without a domain context.');
        }

        static::updateOrCreate(
            ['domain_id' => $domainId, 'key' => $key],
            ['value' => $value, 'group' => $group, 'type' => $type],
        );
    }

    public static function url(string $key, ?int $domainId = null): ?string
    {
        $path = static::get($key, null, $domainId);

        return $path ? Storage::disk('public')->url($path) : null;
    }

    public static function flushCache(?int $domainId = null): void
    {
        if ($domainId !== null) {
            Cache::forget(self::cacheKey($domainId));

            return;
        }

        // Flush every known domain cache entry.
        Domain::query()->pluck('id')->each(
            fn ($id) => Cache::forget(self::cacheKey((int) $id))
        );
        Cache::forget(self::cacheKey(null));
    }
}
