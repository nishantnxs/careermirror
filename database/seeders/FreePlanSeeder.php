<?php

namespace Database\Seeders;

use App\Enums\DurationUnit;
use App\Enums\PlanType;
use App\Models\Domain;
use App\Models\Plan;
use Illuminate\Database\Seeder;

class FreePlanSeeder extends Seeder
{
    public function run(): void
    {
        $plan = Plan::query()->updateOrCreate(
            ['slug' => 'starter'],
            [
                'title' => 'Starter',
                'plan_type' => PlanType::Free,
                'description' => 'Up to 1 active job post',
                'duration_value' => 1,
                'duration_unit' => DurationUnit::Lifetime,
                'currency' => 'CAD',
                'amount' => 0,
                'discount_amount' => null,
                'trial_days' => 0,
                'jobs_allowed' => 1,
                'job_duration_value' => 30,
                'job_duration_unit' => DurationUnit::Day,
                'features' => [
                    '1 active job posting',
                    'Basic applicant tracking',
                    'Candidate messaging',
                ],
                'expiry_date' => null,
                'is_featured' => false,
                'is_active' => true,
                'sort_order' => 0,
            ],
        );

        $domainIds = Domain::query()->pluck('id');

        if ($domainIds->isNotEmpty()) {
            $plan->domains()->syncWithoutDetaching($domainIds->all());
        }
    }
}
