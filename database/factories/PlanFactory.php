<?php

namespace Database\Factories;

use App\Enums\DurationUnit;
use App\Enums\PlanType;
use App\Models\Domain;
use App\Models\Plan;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Plan>
 */
class PlanFactory extends Factory
{
    public function configure(): static
    {
        return $this->afterCreating(function (Plan $plan): void {
            if ($plan->domains()->exists()) {
                return;
            }

            $domainId = Domain::query()->where('is_default', true)->value('id')
                ?? Domain::query()->value('id');

            if ($domainId) {
                $plan->domains()->syncWithoutDetaching([$domainId]);
            }
        });
    }

    /** @return array<string, mixed> */
    public function definition(): array
    {
        $title = Str::title(fake()->unique()->words(2, true));

        return [
            'title' => $title,
            'slug' => Str::slug($title),
            'plan_type' => fake()->randomElement(PlanType::cases()),
            'description' => fake()->sentence(),
            'duration_value' => fake()->numberBetween(1, 12),
            'duration_unit' => DurationUnit::Month,
            'currency' => 'INR',
            'amount' => fake()->randomFloat(2, 199, 9999),
            'trial_days' => 0,
            'jobs_allowed' => fake()->numberBetween(1, 20),
            'job_duration_value' => 30,
            'job_duration_unit' => DurationUnit::Day,
            'features' => fake()->words(3),
            'is_active' => true,
            'sort_order' => 0,
        ];
    }

    public function free(): static
    {
        return $this->state(fn () => [
            'plan_type' => PlanType::Free,
            'amount' => 0,
            'discount_amount' => null,
            'jobs_allowed' => 1,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['is_active' => false]);
    }

    public function expired(): static
    {
        return $this->state(fn () => ['expiry_date' => now()->subDay()->toDateString()]);
    }
}
