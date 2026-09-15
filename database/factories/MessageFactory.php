<?php

namespace Database\Factories;

use App\Enums\MessageSenderType;
use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Message>
 */
class MessageFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'conversation_id' => Conversation::factory(),
            'sender_type' => MessageSenderType::Employer,
            'sender_id' => 1,
            'body' => fake()->paragraph(),
        ];
    }

    public function configure(): static
    {
        return $this->afterMaking(function (Message $message): void {
            if ($message->sender_id !== 1) {
                return;
            }

            $conversation = $message->conversation ?? Conversation::query()->find($message->conversation_id);

            if (! $conversation) {
                return;
            }

            $message->sender_id = $message->sender_type === MessageSenderType::Candidate
                ? $conversation->candidate_id
                : $conversation->employer_id;
        });
    }

    public function fromCandidate(): static
    {
        return $this->state(fn () => ['sender_type' => MessageSenderType::Candidate]);
    }

    public function fromEmployer(): static
    {
        return $this->state(fn () => ['sender_type' => MessageSenderType::Employer]);
    }
}
