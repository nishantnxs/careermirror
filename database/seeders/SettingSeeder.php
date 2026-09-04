<?php

namespace Database\Seeders;

use App\Models\Setting;
use App\Services\SettingService;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * Create a row for every field defined in config/settings.php, using its
     * default value. Existing rows are left untouched.
     */
    public function run(): void
    {
        $seeded = [
            'site_name' => 'CareerMirror',
            'site_tagline' => 'See your career clearly.',
            'contact_email' => 'hello@careermirror.com',
            'footer_copyright' => '© '.date('Y').' CareerMirror. All rights reserved.',
            'meta_title' => 'CareerMirror — See your career clearly',
        ];

        foreach (app(SettingService::class)->fields() as $key => $definition) {
            Setting::firstOrCreate(
                ['key' => $key],
                [
                    'value' => $seeded[$key] ?? $definition['default'] ?? null,
                    'group' => $definition['group'] ?? 'general',
                    'type' => $definition['type'] ?? 'text',
                ],
            );
        }

        Setting::flushCache();
    }
}
