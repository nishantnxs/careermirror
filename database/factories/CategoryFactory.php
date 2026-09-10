<?php

namespace Database\Factories;

use App\Enums\CategoryStatus;
use App\Models\Category;
use App\Models\Employer;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Category>
 */
class CategoryFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        $name = fake()->unique()->words(2, true);

        return [
            'name' => Str::title($name),
            'slug' => null,
            'status' => CategoryStatus::Approved,
            'suggestion_count' => 1,
            'suggested_by_employer_id' => null,
            'reviewed_by_admin_id' => null,
            'reviewed_at' => null,
            'sort_order' => fake()->numberBetween(0, 50),
            'is_active' => true,
        ];
    }

    public function pending(?Employer $employer = null): static
    {
        return $this->state(fn () => [
            'status' => CategoryStatus::Pending,
            'suggested_by_employer_id' => $employer?->id ?? Employer::factory(),
            'suggestion_count' => 1,
            'is_active' => false,
        ]);
    }

    public function rejected(): static
    {
        return $this->state(fn () => [
            'status' => CategoryStatus::Rejected,
            'is_active' => false,
            'reviewed_at' => now(),
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['is_active' => false]);
    }
}
