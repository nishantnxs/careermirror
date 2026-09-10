<?php

namespace Database\Factories;

use App\Enums\SubscriptionStatus;
use App\Models\Employer;
use App\Models\EmployerSubscription;
use App\Models\Order;
use App\Models\Plan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EmployerSubscription>
 */
class EmployerSubscriptionFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'employer_id' => Employer::factory(),
            'order_id' => Order::factory(),
            'plan_id' => Plan::factory(),
            'status' => SubscriptionStatus::Active,
            'starts_at' => now(),
            'ends_at' => now()->addDays(30),
            'jobs_allowed' => 5,
            'jobs_used' => 0,
            'job_duration_days' => 30,
        ];
    }

    public function exhausted(): static
    {
        return $this->state(fn () => [
            'jobs_allowed' => 1,
            'jobs_used' => 1,
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn () => [
            'status' => SubscriptionStatus::Expired,
            'ends_at' => now()->subDay(),
        ]);
    }
}
