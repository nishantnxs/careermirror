<?php

namespace Database\Seeders;

use App\Enums\DurationUnit;
use App\Enums\PlanType;
use App\Models\Plan;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'title' => 'Starter Monthly',
                'plan_type' => PlanType::Basic,
                'description' => 'Get started with the essentials of CareerMirror.',
                'duration_value' => 1,
                'duration_unit' => DurationUnit::Month,
                'amount' => 499.00,
                'discount_amount' => 399.00,
                'trial_days' => 7,
                'features' => ['3 resume reviews', '1 mock interview', 'Email support'],
                'sort_order' => 1,
            ],
            [
                'title' => 'Professional Quarterly',
                'plan_type' => PlanType::Standard,
                'description' => 'For active job seekers who want regular feedback.',
                'duration_value' => 3,
                'duration_unit' => DurationUnit::Month,
                'amount' => 1299.00,
                'trial_days' => 0,
                'features' => ['Unlimited resume reviews', '5 mock interviews', 'Priority email support', 'LinkedIn profile audit'],
                'is_featured' => true,
                'sort_order' => 2,
            ],
            [
                'title' => 'Premium Yearly',
                'plan_type' => PlanType::Premium,
                'description' => 'Everything unlocked for a full year.',
                'duration_value' => 1,
                'duration_unit' => DurationUnit::Year,
                'amount' => 4999.00,
                'discount_amount' => 3999.00,
                'trial_days' => 14,
                'features' => ['Everything in Professional', 'Unlimited mock interviews', '1-on-1 career coaching', 'Dedicated account manager'],
                'expiry_date' => now()->addMonths(6)->toDateString(),
                'sort_order' => 3,
            ],
        ];

        foreach ($plans as $plan) {
            Plan::firstOrCreate(
                ['slug' => Str::slug($plan['title'])],
                $plan + ['currency' => 'INR', 'is_active' => true],
            );
        }
    }
}
