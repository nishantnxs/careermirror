<?php

namespace Database\Factories;

use App\Enums\ApplicationStatus;
use App\Models\Candidate;
use App\Models\CandidateResume;
use App\Models\JobApplication;
use App\Models\JobPosting;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<JobApplication>
 */
class JobApplicationFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'candidate_id' => Candidate::factory(),
            'job_posting_id' => JobPosting::factory(),
            'candidate_resume_id' => CandidateResume::factory(),
            'cover_letter' => fake()->optional()->paragraph(),
            'status' => ApplicationStatus::Applied,
            'applied_at' => now(),
        ];
    }

    public function configure(): static
    {
        return $this->afterMaking(function (JobApplication $application): void {
            if ($application->candidate_resume_id && $application->candidate_id) {
                return;
            }
        })->afterCreating(function (JobApplication $application): void {
            if ($application->resume && $application->resume->candidate_id !== $application->candidate_id) {
                $application->resume->update(['candidate_id' => $application->candidate_id]);
            }
        });
    }

    public function withdrawn(): static
    {
        return $this->state(fn () => [
            'status' => ApplicationStatus::Withdrawn,
            'withdrawn_at' => now(),
        ]);
    }
}
