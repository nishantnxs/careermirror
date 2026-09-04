<?php

namespace App\Http\Requests\Admin;

use App\Enums\DurationUnit;
use App\Enums\PlanType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $planId = $this->route('plan')?->id;

        return [
            'title' => ['required', 'string', 'max:150'],
            'slug' => [
                'nullable', 'string', 'max:170', 'alpha_dash',
                Rule::unique('plans', 'slug')->ignore($planId)->withoutTrashed(),
            ],
            'plan_type' => ['required', Rule::enum(PlanType::class)],
            'description' => ['nullable', 'string', 'max:2000'],

            'duration_unit' => ['required', Rule::enum(DurationUnit::class)],
            'duration_value' => [
                'required_unless:duration_unit,lifetime',
                'nullable', 'integer', 'min:1', 'max:1000',
            ],

            'currency' => ['required', 'string', Rule::in(['INR', 'USD', 'EUR', 'GBP'])],
            'amount' => ['required', 'numeric', 'min:0', 'max:9999999.99'],
            'discount_amount' => ['nullable', 'numeric', 'min:0', 'lt:amount'],

            'trial_days' => ['nullable', 'integer', 'min:0', 'max:365'],
            'features' => ['nullable', 'array'],
            'features.*' => ['nullable', 'string', 'max:255'],

            'expiry_date' => ['nullable', 'date', 'after_or_equal:today'],
            'is_featured' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'discount_amount.lt' => 'The discounted price must be lower than the plan amount.',
            'expiry_date.after_or_equal' => 'The expiry date cannot be in the past.',
            'duration_value.required_unless' => 'Please enter how long the plan lasts.',
        ];
    }

    /**
     * Normalised payload ready to be persisted.
     *
     * @return array<string, mixed>
     */
    public function planData(): array
    {
        $isLifetime = $this->input('duration_unit') === DurationUnit::Lifetime->value;

        $features = collect($this->input('features', []))
            ->map(fn ($feature) => trim((string) $feature))
            ->filter()
            ->values()
            ->all();

        $data = [
            'title' => $this->string('title')->trim()->value(),
            'plan_type' => $this->input('plan_type'),
            'description' => $this->filled('description') ? $this->input('description') : null,
            'duration_value' => $isLifetime ? 1 : (int) $this->input('duration_value'),
            'duration_unit' => $this->input('duration_unit'),
            'currency' => $this->input('currency'),
            'amount' => $this->input('amount'),
            'discount_amount' => $this->filled('discount_amount') ? $this->input('discount_amount') : null,
            'trial_days' => (int) $this->input('trial_days', 0),
            'features' => $features === [] ? null : $features,
            'expiry_date' => $this->filled('expiry_date') ? $this->input('expiry_date') : null,
            'is_featured' => $this->boolean('is_featured'),
            'is_active' => $this->boolean('is_active'),
            'sort_order' => (int) $this->input('sort_order', 0),
        ];

        if ($this->filled('slug')) {
            $data['slug'] = $this->input('slug');
        }

        return $data;
    }
}
