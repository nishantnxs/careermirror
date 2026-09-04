<?php

namespace App\Http\Requests\Admin;

use App\Services\SettingService;
use Illuminate\Foundation\Http\FormRequest;

class WebsiteSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return app(SettingService::class)->rules();
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        $attributes = [];

        foreach (app(SettingService::class)->fields() as $key => $definition) {
            $attributes[$key] = strtolower($definition['label'] ?? $key);
        }

        return $attributes;
    }
}
