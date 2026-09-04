<?php

use App\Models\Setting;

if (! function_exists('setting')) {
    /**
     * Read a website setting, falling back to $default when unset.
     */
    function setting(string $key, mixed $default = null): mixed
    {
        return Setting::get($key, $default);
    }
}

if (! function_exists('setting_url')) {
    /**
     * Public URL for a setting that stores an uploaded file path.
     */
    function setting_url(string $key): ?string
    {
        return Setting::url($key);
    }
}
