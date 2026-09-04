<?php

/*
|--------------------------------------------------------------------------
| Website Settings Definition
|--------------------------------------------------------------------------
|
| The admin "Website Settings" screen is generated from this file. Each entry
| under "fields" becomes a form input, a validation rule and a row in the
| `settings` table, so a new setting only needs a new line here.
|
| Supported types: text, textarea, email, tel, url, number, password, image,
| select, boolean, color.
|
*/

return [

    'groups' => [

        'general' => [
            'label' => 'General',
            'icon' => 'bi-sliders',
            'fields' => [
                'site_name' => [
                    'label' => 'Site Name',
                    'type' => 'text',
                    'rules' => ['required', 'string', 'max:150'],
                    'default' => 'CareerMirror',
                    'width' => 6,
                ],
                'site_tagline' => [
                    'label' => 'Tagline',
                    'type' => 'text',
                    'rules' => ['nullable', 'string', 'max:255'],
                    'width' => 6,
                ],
                'site_description' => [
                    'label' => 'Short Description',
                    'type' => 'textarea',
                    'rules' => ['nullable', 'string', 'max:1000'],
                ],
                'site_logo' => [
                    'label' => 'Logo',
                    'type' => 'image',
                    'rules' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp,svg', 'max:2048'],
                    'hint' => 'PNG or SVG, recommended 240x60px.',
                    'width' => 4,
                ],
                'site_logo_dark' => [
                    'label' => 'Logo (dark background)',
                    'type' => 'image',
                    'rules' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp,svg', 'max:2048'],
                    'width' => 4,
                ],
                'site_favicon' => [
                    'label' => 'Favicon',
                    'type' => 'image',
                    'rules' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp,ico', 'max:1024'],
                    'hint' => '32x32px or 64x64px.',
                    'width' => 4,
                ],
                'footer_copyright' => [
                    'label' => 'Footer Copyright Text',
                    'type' => 'text',
                    'rules' => ['nullable', 'string', 'max:255'],
                ],
            ],
        ],

        'contact' => [
            'label' => 'Contact',
            'icon' => 'bi-telephone',
            'fields' => [
                'contact_email' => [
                    'label' => 'Contact Email',
                    'type' => 'email',
                    'rules' => ['nullable', 'email', 'max:150'],
                    'width' => 6,
                ],
                'support_email' => [
                    'label' => 'Support Email',
                    'type' => 'email',
                    'rules' => ['nullable', 'email', 'max:150'],
                    'width' => 6,
                ],
                'contact_phone' => [
                    'label' => 'Phone',
                    'type' => 'tel',
                    'rules' => ['nullable', 'string', 'max:30'],
                    'width' => 6,
                ],
                'contact_whatsapp' => [
                    'label' => 'WhatsApp',
                    'type' => 'tel',
                    'rules' => ['nullable', 'string', 'max:30'],
                    'width' => 6,
                ],
                'contact_address' => [
                    'label' => 'Address',
                    'type' => 'textarea',
                    'rules' => ['nullable', 'string', 'max:500'],
                ],
                'google_map_embed' => [
                    'label' => 'Google Map Embed URL',
                    'type' => 'url',
                    'rules' => ['nullable', 'url', 'max:1000'],
                ],
            ],
        ],

        'social' => [
            'label' => 'Social Links',
            'icon' => 'bi-share',
            'fields' => [
                'social_facebook' => [
                    'label' => 'Facebook',
                    'type' => 'url',
                    'rules' => ['nullable', 'url', 'max:255'],
                    'width' => 6,
                ],
                'social_instagram' => [
                    'label' => 'Instagram',
                    'type' => 'url',
                    'rules' => ['nullable', 'url', 'max:255'],
                    'width' => 6,
                ],
                'social_linkedin' => [
                    'label' => 'LinkedIn',
                    'type' => 'url',
                    'rules' => ['nullable', 'url', 'max:255'],
                    'width' => 6,
                ],
                'social_twitter' => [
                    'label' => 'X (Twitter)',
                    'type' => 'url',
                    'rules' => ['nullable', 'url', 'max:255'],
                    'width' => 6,
                ],
                'social_youtube' => [
                    'label' => 'YouTube',
                    'type' => 'url',
                    'rules' => ['nullable', 'url', 'max:255'],
                    'width' => 6,
                ],
            ],
        ],

        'seo' => [
            'label' => 'SEO',
            'icon' => 'bi-graph-up-arrow',
            'fields' => [
                'meta_title' => [
                    'label' => 'Meta Title',
                    'type' => 'text',
                    'rules' => ['nullable', 'string', 'max:255'],
                ],
                'meta_description' => [
                    'label' => 'Meta Description',
                    'type' => 'textarea',
                    'rules' => ['nullable', 'string', 'max:500'],
                ],
                'meta_keywords' => [
                    'label' => 'Meta Keywords',
                    'type' => 'textarea',
                    'rules' => ['nullable', 'string', 'max:500'],
                    'hint' => 'Comma separated.',
                ],
                'og_image' => [
                    'label' => 'Social Share Image',
                    'type' => 'image',
                    'rules' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:2048'],
                    'hint' => 'Recommended 1200x630px.',
                    'width' => 6,
                ],
                'google_analytics_id' => [
                    'label' => 'Google Analytics ID',
                    'type' => 'text',
                    'rules' => ['nullable', 'string', 'max:50'],
                    'width' => 6,
                ],
            ],
        ],

        'mail' => [
            'label' => 'Mail',
            'icon' => 'bi-envelope',
            'fields' => [
                'mail_from_name' => [
                    'label' => 'From Name',
                    'type' => 'text',
                    'rules' => ['nullable', 'string', 'max:150'],
                    'width' => 6,
                ],
                'mail_from_address' => [
                    'label' => 'From Address',
                    'type' => 'email',
                    'rules' => ['nullable', 'email', 'max:150'],
                    'width' => 6,
                ],
                'mail_host' => [
                    'label' => 'SMTP Host',
                    'type' => 'text',
                    'rules' => ['nullable', 'string', 'max:150'],
                    'width' => 6,
                ],
                'mail_port' => [
                    'label' => 'SMTP Port',
                    'type' => 'number',
                    'rules' => ['nullable', 'integer', 'min:1', 'max:65535'],
                    'width' => 6,
                ],
                'mail_username' => [
                    'label' => 'SMTP Username',
                    'type' => 'text',
                    'rules' => ['nullable', 'string', 'max:150'],
                    'width' => 6,
                ],
                'mail_password' => [
                    'label' => 'SMTP Password',
                    'type' => 'password',
                    'rules' => ['nullable', 'string', 'max:150'],
                    'hint' => 'Leave blank to keep the saved password.',
                    'width' => 6,
                ],
                'mail_encryption' => [
                    'label' => 'Encryption',
                    'type' => 'select',
                    'rules' => ['nullable', 'in:tls,ssl,none'],
                    'options' => ['tls' => 'TLS', 'ssl' => 'SSL', 'none' => 'None'],
                    'width' => 6,
                ],
            ],
        ],

        'system' => [
            'label' => 'System',
            'icon' => 'bi-gear',
            'fields' => [
                'default_currency' => [
                    'label' => 'Default Currency',
                    'type' => 'select',
                    'rules' => ['nullable', 'in:INR,USD,EUR,GBP'],
                    'options' => ['INR' => 'INR (₹)', 'USD' => 'USD ($)', 'EUR' => 'EUR (€)', 'GBP' => 'GBP (£)'],
                    'default' => 'INR',
                    'width' => 6,
                ],
                'primary_color' => [
                    'label' => 'Primary Brand Colour',
                    'type' => 'color',
                    'rules' => ['nullable', 'string', 'max:20'],
                    'default' => '#4f46e5',
                    'width' => 6,
                ],
                'maintenance_mode' => [
                    'label' => 'Maintenance Mode',
                    'type' => 'boolean',
                    'rules' => ['nullable', 'boolean'],
                    'hint' => 'Shows a maintenance notice on the public website.',
                    'width' => 6,
                ],
                'allow_registration' => [
                    'label' => 'Allow User Registration',
                    'type' => 'boolean',
                    'rules' => ['nullable', 'boolean'],
                    'default' => '1',
                    'width' => 6,
                ],
                'maintenance_message' => [
                    'label' => 'Maintenance Message',
                    'type' => 'textarea',
                    'rules' => ['nullable', 'string', 'max:500'],
                ],
            ],
        ],

    ],

];
