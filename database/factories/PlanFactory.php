<?php

namespace Database\Factories;

use App\Enums\DurationUnit;
use App\Enums\PlanType;
use App\Models\Plan;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Plan>
 */
class PlanFactory extends Factory
{
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
            'features' => fake()->words(3),
            'is_active' => true,
            'sort_order' => 0,
        ];
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
