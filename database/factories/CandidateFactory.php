<?php

namespace Database\Factories;

use App\Enums\AccountStatus;
use App\Models\Candidate;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Candidate>
 */
class CandidateFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->numerify('##########'),
            'password' => 'password',
            'status' => AccountStatus::Active,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['status' => AccountStatus::Inactive]);
    }

    public function suspended(): static
    {
        return $this->state(fn () => ['status' => AccountStatus::Suspended]);
    }
}
