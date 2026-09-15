<?php

namespace Tests\Feature\Employer;

use App\Enums\ApplicationStatus;
use App\Enums\JobStatus;
use App\Enums\MessageSenderType;
use App\Models\Candidate;
use App\Models\CandidateResume;
use App\Models\Conversation;
use App\Models\Domain;
use App\Models\Employer;
use App\Models\JobApplication;
use App\Models\JobPosting;
use App\Models\Message;
use App\Notifications\Messaging\NewMessageNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class MessagingTest extends TestCase
{
    use RefreshDatabase;

    private function applicationFor(Employer $employer, Candidate $candidate): JobApplication
    {
        $domain = Domain::query()->where('is_default', true)->firstOrFail();

        $job = JobPosting::factory()->create([
            'employer_id' => $employer->id,
            'status' => JobStatus::Published,
            'published_at' => now(),
            'expires_at' => now()->addDays(10),
        ]);
        $job->domains()->sync([$domain->id]);

        $resume = CandidateResume::factory()->for($candidate)->create();

        return JobApplication::factory()->create([
            'candidate_id' => $candidate->id,
            'job_posting_id' => $job->id,
            'candidate_resume_id' => $resume->id,
            'status' => ApplicationStatus::Applied,
        ]);
    }

    public function test_employer_can_start_conversation_with_applicant(): void
    {
        Notification::fake();

        $employer = Employer::factory()->create();
        $candidate = Candidate::factory()->create();
        $application = $this->applicationFor($employer, $candidate);

        $this->actingAs($employer, 'employer')
            ->post('/employer/messages', [
                'job_application_id' => $application->id,
                'candidate_id' => $candidate->id,
                'subject' => 'Interview availability',
                'body' => 'Are you free next week for a call?',
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('conversations', [
            'employer_id' => $employer->id,
            'candidate_id' => $candidate->id,
            'subject' => 'Interview availability',
        ]);

        $this->assertDatabaseHas('messages', [
            'body' => 'Are you free next week for a call?',
            'sender_type' => 'employer',
            'sender_id' => $employer->id,
        ]);

        Notification::assertSentTo($candidate, NewMessageNotification::class);
    }

    public function test_employer_cannot_message_candidate_who_did_not_apply(): void
    {
        $employer = Employer::factory()->create();
        $candidate = Candidate::factory()->create();

        $this->actingAs($employer, 'employer')
            ->post('/employer/messages', [
                'job_application_id' => 999,
                'candidate_id' => $candidate->id,
                'body' => 'Hello',
            ])
            ->assertSessionHasErrors('job_application_id');
    }

    public function test_employer_can_reply_in_existing_conversation(): void
    {
        Notification::fake();

        $employer = Employer::factory()->create();
        $candidate = Candidate::factory()->create();
        $this->applicationFor($employer, $candidate);

        $conversation = Conversation::factory()->create([
            'employer_id' => $employer->id,
            'candidate_id' => $candidate->id,
        ]);

        $this->actingAs($employer, 'employer')
            ->post('/employer/messages/'.$conversation->id, [
                'body' => 'Following up on your application.',
            ])
            ->assertRedirect('/employer/messages/'.$conversation->id);

        $this->assertTrue(
            Message::query()
                ->where('conversation_id', $conversation->id)
                ->where('body', 'Following up on your application.')
                ->exists()
        );
    }

    public function test_employer_can_poll_unread_count_and_message_updates(): void
    {
        $employer = Employer::factory()->create();
        $candidate = Candidate::factory()->create();
        $this->applicationFor($employer, $candidate);

        $conversation = Conversation::factory()->create([
            'employer_id' => $employer->id,
            'candidate_id' => $candidate->id,
            'employer_last_read_at' => null,
        ]);

        $first = Message::factory()->create([
            'conversation_id' => $conversation->id,
            'sender_type' => MessageSenderType::Candidate,
            'sender_id' => $candidate->id,
            'body' => 'Candidate first',
            'created_at' => now()->subMinute(),
        ]);

        $second = Message::factory()->create([
            'conversation_id' => $conversation->id,
            'sender_type' => MessageSenderType::Candidate,
            'sender_id' => $candidate->id,
            'body' => 'Candidate second',
            'created_at' => now(),
        ]);

        $this->actingAs($employer, 'employer')
            ->getJson('/employer/messages/unread-count')
            ->assertOk()
            ->assertJson(['unread_count' => 2]);

        $this->actingAs($employer, 'employer')
            ->getJson('/employer/messages/'.$conversation->id.'/updates?after_id='.$first->id)
            ->assertOk()
            ->assertJsonPath('messages.0.id', $second->id)
            ->assertJsonPath('messages.0.body', 'Candidate second')
            ->assertJsonPath('messages.0.mine', false)
            ->assertJsonPath('unread_count', 0);

        $this->assertNotNull($conversation->fresh()->employer_last_read_at);
    }

    public function test_employer_can_reply_via_json(): void
    {
        Notification::fake();

        $employer = Employer::factory()->create();
        $candidate = Candidate::factory()->create();
        $this->applicationFor($employer, $candidate);

        $conversation = Conversation::factory()->create([
            'employer_id' => $employer->id,
            'candidate_id' => $candidate->id,
        ]);

        $this->actingAs($employer, 'employer')
            ->postJson('/employer/messages/'.$conversation->id, [
                'body' => 'Live reply from employer.',
            ])
            ->assertOk()
            ->assertJsonPath('message.body', 'Live reply from employer.')
            ->assertJsonPath('message.mine', true)
            ->assertJsonStructure(['message' => ['id', 'body', 'mine', 'sender_label', 'created_at'], 'unread_count']);

        $this->assertDatabaseHas('messages', [
            'conversation_id' => $conversation->id,
            'sender_type' => MessageSenderType::Employer->value,
            'body' => 'Live reply from employer.',
        ]);

        Notification::assertSentTo($candidate, NewMessageNotification::class);
    }

    public function test_employer_form_ajax_reply_returns_json_once(): void
    {
        Notification::fake();

        $employer = Employer::factory()->create();
        $candidate = Candidate::factory()->create();
        $this->applicationFor($employer, $candidate);

        $conversation = Conversation::factory()->create([
            'employer_id' => $employer->id,
            'candidate_id' => $candidate->id,
        ]);

        $this->actingAs($employer, 'employer')
            ->post('/employer/messages/'.$conversation->id, [
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
