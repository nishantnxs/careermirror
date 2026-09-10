<?php

namespace Database\Factories;

use App\Enums\JobStatus;
use App\Models\Employer;
use App\Models\EmployerSubscription;
use App\Models\JobPosting;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<JobPosting>
 */
class JobPostingFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'employer_id' => Employer::factory(),
            'employer_subscription_id' => EmployerSubscription::factory(),
            'title' => fake()->jobTitle(),
            'description' => fake()->paragraphs(2, true),
            'location' => fake()->city(),
            'job_type' => 'full-time',
            'experience_level' => 'mid',
            'salary_min' => 30000,
            'salary_max' => 60000,
            'currency' => 'INR',
            'status' => JobStatus::Published,
            'published_at' => now(),
            'expires_at' => now()->addDays(30),
        ];
    }
}
