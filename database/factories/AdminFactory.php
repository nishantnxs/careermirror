<?php

namespace Database\Factories;

use App\Models\Admin;
use App\Models\AdminRole;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Admin>
 */
class AdminFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->numerify('##########'),
            'password' => 'password',
            'is_active' => true,
            'is_super_admin' => true,
            'admin_role_id' => null,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['is_active' => false]);
    }

    public function superAdmin(): static
    {
        return $this->state(fn () => [
            'is_super_admin' => true,
            'admin_role_id' => null,
        ]);
    }

    public function moderator(?AdminRole $role = null): static
    {
        return $this->state(fn () => [
            'is_super_admin' => false,
            'admin_role_id' => $role?->id ?? AdminRole::factory(),
        ]);
    }
}
