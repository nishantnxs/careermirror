<?php

namespace Database\Factories;

use App\Enums\DomainStatus;
use App\Models\Domain;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Domain>
 */
class DomainFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        $host = fake()->unique()->domainName();

        return [
            'name' => $host,
            'host' => $host,
            'url' => 'https://'.$host,
            'website_name' => fake()->company().' Jobs',
            'logo_path' => null,
            'favicon_path' => null,
            'status' => DomainStatus::Active,
            'is_default' => false,
            'seo_title' => fake()->sentence(3),
            'seo_description' => fake()->sentence(8),
            'seo_keywords' => 'jobs, careers',
        ];
    }

    public function default(): static
    {
        return $this->state(fn () => ['is_default' => true]);
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['status' => DomainStatus::Inactive]);
    }
}
