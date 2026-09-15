<?php

namespace Tests\Feature\Candidate;

use App\Enums\MessageSenderType;
use App\Models\Candidate;
use App\Models\Conversation;
use App\Models\Employer;
use App\Models\Message;
use App\Notifications\Messaging\NewMessageNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class MessagingTest extends TestCase
{
    use RefreshDatabase;

    public function test_candidate_can_view_inbox_and_conversation(): void
    {
        $candidate = Candidate::factory()->create();
        $employer = Employer::factory()->create(['company_name' => 'Acme Hiring']);

        $conversation = Conversation::factory()->create([
            'employer_id' => $employer->id,
            'candidate_id' => $candidate->id,
            'subject' => 'Next steps',
            'candidate_last_read_at' => null,
        ]);

        Message::factory()->create([
            'conversation_id' => $conversation->id,
            'sender_type' => MessageSenderType::Employer,
            'sender_id' => $employer->id,
            'body' => 'Thanks for applying to our role.',
        ]);

        $this->actingAs($candidate, 'candidate')
            ->get('/candidate/messages')
            ->assertOk()
            ->assertSee('Acme Hiring')
            ->assertSee('Thanks for applying to our role.');

        $this->actingAs($candidate, 'candidate')
            ->get('/candidate/messages/'.$conversation->id)
            ->assertOk()
            ->assertSee('Thanks for applying to our role.');

        $this->assertNotNull($conversation->fresh()->candidate_last_read_at);
    }

    public function test_candidate_can_reply_to_conversation(): void
    {
        Notification::fake();

        $candidate = Candidate::factory()->create();
        $employer = Employer::factory()->create();

        $conversation = Conversation::factory()->create([
            'employer_id' => $employer->id,
            'candidate_id' => $candidate->id,
        ]);

        $this->actingAs($candidate, 'candidate')
            ->post('/candidate/messages/'.$conversation->id, [
                'body' => 'Yes, I am available on Tuesday.',
            ])
            ->assertRedirect('/candidate/messages/'.$conversation->id)
            ->assertSessionHas('success');

        $this->assertDatabaseHas('messages', [
            'conversation_id' => $conversation->id,
            'sender_type' => MessageSenderType::Candidate->value,
            'sender_id' => $candidate->id,
            'body' => 'Yes, I am available on Tuesday.',
        ]);

        Notification::assertSentTo($employer, NewMessageNotification::class);
    }

    public function test_candidate_cannot_open_another_candidates_conversation(): void
    {
        $owner = Candidate::factory()->create();
        $intruder = Candidate::factory()->create();
        $employer = Employer::factory()->create();

        $conversation = Conversation::factory()->create([
            'employer_id' => $employer->id,
            'candidate_id' => $owner->id,
        ]);

        $this->actingAs($intruder, 'candidate')
            ->get('/candidate/messages/'.$conversation->id)
            ->assertNotFound();
    }

    public function test_candidate_can_poll_unread_count_and_message_updates(): void
    {
        $candidate = Candidate::factory()->create();
        $employer = Employer::factory()->create(['company_name' => 'Acme Hiring']);

        $conversation = Conversation::factory()->create([
            'employer_id' => $employer->id,
            'candidate_id' => $candidate->id,
            'candidate_last_read_at' => null,
        ]);

        $first = Message::factory()->create([
            'conversation_id' => $conversation->id,
            'sender_type' => MessageSenderType::Employer,
            'sender_id' => $employer->id,
            'body' => 'First note',
            'created_at' => now()->subMinute(),
        ]);

        $second = Message::factory()->create([
            'conversation_id' => $conversation->id,
            'sender_type' => MessageSenderType::Employer,
            'sender_id' => $employer->id,
            'body' => 'Second note',
            'created_at' => now(),
        ]);

        $this->actingAs($candidate, 'candidate')
            ->getJson('/candidate/messages/unread-count')
            ->assertOk()
            ->assertJson(['unread_count' => 2]);

        $this->actingAs($candidate, 'candidate')
            ->getJson('/candidate/messages/'.$conversation->id.'/updates?after_id='.$first->id)
            ->assertOk()
            ->assertJsonPath('messages.0.id', $second->id)
            ->assertJsonPath('messages.0.body', 'Second note')
            ->assertJsonPath('messages.0.mine', false)
            ->assertJsonPath('unread_count', 0);

        $this->assertNotNull($conversation->fresh()->candidate_last_read_at);
    }

    public function test_candidate_can_reply_via_json(): void
    {
        Notification::fake();

        $candidate = Candidate::factory()->create();
        $employer = Employer::factory()->create();

        $conversation = Conversation::factory()->create([
            'employer_id' => $employer->id,
            'candidate_id' => $candidate->id,
        ]);

        $this->actingAs($candidate, 'candidate')
            ->postJson('/candidate/messages/'.$conversation->id, [
                'body' => 'Live reply from candidate.',
            ])
            ->assertOk()
            ->assertJsonPath('message.body', 'Live reply from candidate.')
            ->assertJsonPath('message.mine', true)
            ->assertJsonStructure(['message' => ['id', 'body', 'mine', 'sender_label', 'created_at'], 'unread_count']);

        $this->assertDatabaseHas('messages', [
            'conversation_id' => $conversation->id,
            'sender_type' => MessageSenderType::Candidate->value,
            'body' => 'Live reply from candidate.',
        ]);

        Notification::assertSentTo($employer, NewMessageNotification::class);
    }

    public function test_candidate_form_ajax_reply_returns_json_once(): void
    {
        Notification::fake();

        $candidate = Candidate::factory()->create();
        $employer = Employer::factory()->create();

        $conversation = Conversation::factory()->create([
            'employer_id' => $employer->id,
            'candidate_id' => $candidate->id,
        ]);

        $this->actingAs($candidate, 'candidate')
            ->post('/candidate/messages/'.$conversation->id, [
                'body' => 'Ajax form reply.',
            ], [
                'Accept' => 'application/json',
                'X-Requested-With' => 'XMLHttpRequest',
            ])
            ->assertOk()
            ->assertJsonPath('message.body', 'Ajax form reply.');

        $this->assertSame(1, Message::query()->where('conversation_id', $conversation->id)->count());
    }
}
