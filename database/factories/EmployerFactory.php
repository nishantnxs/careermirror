<?php

namespace Database\Factories;

use App\Enums\AccountStatus;
use App\Models\Domain;
use App\Models\Employer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Employer>
 */
class EmployerFactory extends Factory
{
    public function configure(): static
    {
        return $this->afterCreating(function (Employer $employer): void {
            if ($employer->domains()->exists()) {
                return;
            }

            $domainId = Domain::query()->where('is_default', true)->value('id')
                ?? Domain::query()->value('id');

            if ($domainId) {
                $employer->domains()->syncWithoutDetaching([$domainId]);
            }
        });
    }

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'company_name' => fake()->company(),
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
