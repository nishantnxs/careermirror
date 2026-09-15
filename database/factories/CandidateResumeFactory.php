<?php

namespace Database\Factories;

use App\Enums\ResumeSource;
use App\Models\Candidate;
use App\Models\CandidateResume;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CandidateResume>
 */
class CandidateResumeFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'candidate_id' => Candidate::factory(),
            'title' => fake()->jobTitle().' Resume',
            'source' => ResumeSource::Builder,
            'is_default' => false,
            'content' => array_replace(CandidateResume::emptyContent(), [
                'summary' => fake()->paragraph(),
                'skills' => fake()->words(5),
                'experience' => [[
                    'title' => fake()->jobTitle(),
                    'company' => fake()->company(),
                    'location' => fake()->city(),
                    'start_date' => '2020-01',
                    'end_date' => '2022-06',
                    'current' => false,
                    'description' => fake()->sentence(),
                ]],
                'education' => [[
                    'degree' => 'B.Tech',
                    'institution' => fake()->company().' University',
                    'location' => fake()->city(),
                    'start_date' => '2016',
                    'end_date' => '2020',
                    'description' => '',
                ]],
            ]),
        ];
    }

    public function uploaded(): static
    {
        return $this->state(fn () => [
            'source' => ResumeSource::Upload,
            'content' => null,
            'file_path' => 'resumes/example.pdf',
            'original_filename' => 'resume.pdf',
            'mime_type' => 'application/pdf',
            'file_size' => 1024,
        ]);
    }

    public function default(): static
    {
        return $this->state(fn () => ['is_default' => true]);
    }
}
