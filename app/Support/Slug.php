<?php

namespace App\Support;

use Illuminate\Support\Str;

class Slug
{
    /**
     * Build a URL-safe slug from a display name.
     */
    public static function from(string $name, string $fallback = 'item'): string
    {
        $slug = Str::slug(trim($name));

        return $slug !== '' ? $slug : $fallback;
    }

    /**
     * Build a unique slug, appending -2, -3, ... while $exists returns true.
     *
     * @param  callable(string): bool  $exists
     */
    public static function unique(string $name, callable $exists, string $fallback = 'item'): string
    {
        $base = self::from($name, $fallback);
        $slug = $base;
        $suffix = 1;

        while ($exists($slug)) {
            $slug = $base.'-'.++$suffix;
        }

        return $slug;
    }
}
