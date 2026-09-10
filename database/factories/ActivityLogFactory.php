<?php

namespace Database\Factories;

use App\Enums\ActivityAction;
use App\Models\ActivityLog;
use App\Models\Admin;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ActivityLog>
 */
class ActivityLogFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'actor_type' => Admin::class,
            'actor_id' => Admin::factory(),
            'actor_name' => fake()->name(),
            'actor_guard' => 'admin',
            'action' => ActivityAction::SettingsUpdated,
            'description' => ActivityAction::SettingsUpdated->label(),
            'subject_type' => null,
            'subject_id' => null,
            'subject_label' => null,
            'old_values' => ['site_name' => 'Old'],
            'new_values' => ['site_name' => 'New'],
            'properties' => null,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'PHPUnit',
            'created_at' => now(),
        ];
    }
}
