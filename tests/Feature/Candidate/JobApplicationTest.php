<?php

namespace Tests\Feature\Candidate;

use App\Enums\ApplicationStatus;
use App\Enums\JobStatus;
use App\Models\Candidate;
use App\Models\CandidateResume;
use App\Models\Domain;
use App\Models\JobApplication;
use App\Models\JobPosting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JobApplicationTest extends TestCase
{
    use RefreshDatabase;

    private function availableJob(): JobPosting
    {
        $domain = Domain::query()->where('is_default', true)->firstOrFail();

        $job = JobPosting::factory()->create([
            'title' => 'Apply Me Role',
            'status' => JobStatus::Published,
            'published_at' => now(),
            'expires_at' => now()->addDays(10),
        ]);

        $job->domains()->sync([$domain->id]);

        return $job;
    }

    public function test_apply_forms_show_resume_title_and_type(): void
    {
        $candidate = Candidate::factory()->create();
        CandidateResume::factory()->for($candidate)->default()->create([
            'title' => 'Voluptatum corporis',
        ]);
        CandidateResume::factory()->for($candidate)->uploaded()->create([
            'title' => 'Resume new',
        ]);
        $job = $this->availableJob();

        $this->actingAs($candidate, 'candidate')
            ->get('/jobs')
            ->assertOk()
            ->assertSee('Voluptatum corporis (default) — Built in CareerMirror')
            ->assertSee('Resume new — Uploaded file');

        $this->actingAs($candidate, 'candidate')
            ->get('/candidate/jobs/'.$job->id)
            ->assertOk()
            ->assertSee('Voluptatum corporis (default) — Built in CareerMirror')
            ->assertSee('Resume new — Uploaded file');
    }

    public function test_candidate_can_apply_with_selected_resume(): void
    {
        $candidate = Candidate::factory()->create();
        $resume = CandidateResume::factory()->for($candidate)->default()->create([
            'title' => 'Main Resume',
        ]);
        $job = $this->availableJob();

        $this->actingAs($candidate, 'candidate')
            ->post('/candidate/jobs/'.$job->id.'/apply', [
                'candidate_resume_id' => $resume->id,
                'cover_letter' => 'I am a great fit.',
            ])
            ->assertRedirect('/candidate/applications')
            ->assertSessionHas('success');

        $this->assertDatabaseHas('job_applications', [
            'candidate_id' => $candidate->id,
            'job_posting_id' => $job->id,
            'candidate_resume_id' => $resume->id,
            'status' => ApplicationStatus::Applied->value,
        ]);

        $this->actingAs($candidate, 'candidate')
            ->get('/candidate/applications')
            ->assertOk()
            ->assertSee('My applications')
            ->assertSee('Apply Me Role')
            ->assertSee('submitted');
    }

    public function test_candidate_cannot_apply_twice_to_the_same_job(): void
    {
        $candidate = Candidate::factory()->create();
        $resume = CandidateResume::factory()->for($candidate)->create();
        $job = $this->availableJob();

        JobApplication::factory()->create([
            'candidate_id' => $candidate->id,
            'job_posting_id' => $job->id,
            'candidate_resume_id' => $resume->id,
            'status' => ApplicationStatus::Applied,
        ]);

        $this->actingAs($candidate, 'candidate')
            ->post('/candidate/jobs/'.$job->id.'/apply', [
                'candidate_resume_id' => $resume->id,
            ])
            ->assertSessionHasErrors('job');
    }

    public function test_candidate_can_withdraw_an_application(): void
    {
        $candidate = Candidate::factory()->create();
        $resume = CandidateResume::factory()->for($candidate)->create();
        $job = $this->availableJob();

        $application = JobApplication::factory()->create([
            'candidate_id' => $candidate->id,
            'job_posting_id' => $job->id,
            'candidate_resume_id' => $resume->id,
        ]);

        $this->actingAs($candidate, 'candidate')
            ->post('/candidate/applications/'.$application->id.'/withdraw')
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertSame(ApplicationStatus::Withdrawn, $application->fresh()->status);
    }

    public function test_candidate_cannot_view_another_candidates_application(): void
    {
        $owner = Candidate::factory()->create();
        $intruder = Candidate::factory()->create();
        $resume = CandidateResume::factory()->for($owner)->create();
        $job = $this->availableJob();

        $application = JobApplication::factory()->create([
            'candidate_id' => $owner->id,
            'job_posting_id' => $job->id,
            'candidate_resume_id' => $resume->id,
        ]);

        $this->actingAs($intruder, 'candidate')
            ->get('/candidate/applications/'.$application->id)
            ->assertNotFound();
    }

    public function test_withdrawn_application_can_be_resubmitted(): void
    {
        $candidate = Candidate::factory()->create();
        $resume = CandidateResume::factory()->for($candidate)->create();
        $job = $this->availableJob();

        JobApplication::factory()->withdrawn()->create([
            'candidate_id' => $candidate->id,
            'job_posting_id' => $job->id,
            'candidate_resume_id' => $resume->id,
        ]);

        $this->actingAs($candidate, 'candidate')
            ->post('/candidate/jobs/'.$job->id.'/apply', [
                'candidate_resume_id' => $resume->id,
                'cover_letter' => 'Reapplying',
            ])
            ->assertRedirect('/candidate/applications');

        $this->assertDatabaseHas('job_applications', [
            'candidate_id' => $candidate->id,
            'job_posting_id' => $job->id,
            'status' => ApplicationStatus::Applied->value,
        ]);
        $this->assertSame(1, JobApplication::query()->count());
    }
}
