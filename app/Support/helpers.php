<?php

use App\Models\Domain;
use App\Models\Setting;
use App\Services\DomainService;
use App\Support\CurrentDomain;
use App\Support\Slug;

if (! function_exists('setting')) {
    /**
     * Read a website setting for the current (or given) domain.
     */
    function setting(string $key, mixed $default = null, ?int $domainId = null): mixed
    {
        return Setting::get($key, $default, $domainId);
    }
}

if (! function_exists('setting_url')) {
    /**
     * Public URL for a setting that stores an uploaded file path.
     */
    function setting_url(string $key, ?int $domainId = null): ?string
    {
        return Setting::url($key, $domainId);
    }
}

if (! function_exists('current_domain')) {
    function current_domain(): ?Domain
    {
        return app(CurrentDomain::class)->get();
    }
}

if (! function_exists('current_domain_id')) {
    function current_domain_id(): ?int
    {
        return app(CurrentDomain::class)->id();
    }
}

if (! function_exists('resolve_settings_domain_id')) {
    /**
     * Resolve which domain's settings should be read/written.
     */
    function resolve_settings_domain_id(?int $domainId = null): ?int
    {
        if ($domainId !== null) {
            return $domainId;
        }

        if ($id = current_domain_id()) {
            return $id;
        }

        try {
            $adminId = app(DomainService::class)->adminContextDomainId();

            if ($adminId !== null) {
                return $adminId;
            }
        } catch (Throwable) {
            // Container may not be ready during early boot.
        }

        return Domain::query()->where('is_default', true)->value('id')
            ?? Domain::query()->value('id');
    }
}

if (! function_exists('slug_from')) {
    /**
     * Build a URL-safe slug from a display name.
     */
    function slug_from(string $name, string $fallback = 'item'): string
    {
        return Slug::from($name, $fallback);
    }
}

if (! function_exists('unique_slug')) {
    /**
     * Build a unique slug, appending numeric suffixes while $exists returns true.
     *
     * @param  callable(string): bool  $exists
     */
    function unique_slug(string $name, callable $exists, string $fallback = 'item'): string
    {
        return Slug::unique($name, $exists, $fallback);
    }
}
