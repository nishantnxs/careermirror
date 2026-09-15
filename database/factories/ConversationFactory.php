<?php

namespace Database\Factories;

use App\Models\Candidate;
use App\Models\Conversation;
use App\Models\Employer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Conversation>
 */
class ConversationFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'employer_id' => Employer::factory(),
            'candidate_id' => Candidate::factory(),
            'subject' => fake()->sentence(3),
            'last_message_at' => now(),
            'employer_last_read_at' => now(),
            'candidate_last_read_at' => null,
        ];
    }
}
