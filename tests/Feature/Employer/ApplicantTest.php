<?php

namespace Tests\Feature\Employer;

use App\Enums\ApplicationStatus;
use App\Enums\JobStatus;
use App\Models\Candidate;
use App\Models\CandidateResume;
use App\Models\Employer;
use App\Models\JobApplication;
use App\Models\JobPosting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApplicantTest extends TestCase
{
    use RefreshDatabase;

    private function applicationFor(Employer $employer, array $overrides = []): JobApplication
    {
        $candidate = Candidate::factory()->create([
            'name' => 'Abel Poole',
            'email' => 'abel@example.test',
            'current_title' => 'Frontend Web Developer',
            'location' => 'Toronto, Ontario, Canada',
        ]);
        $resume = CandidateResume::factory()->for($candidate)->create([
            'title' => 'Voluptatum corporis',
        ]);
        $job = JobPosting::factory()->for($employer)->create([
            'title' => 'Software Engineer',
            'status' => JobStatus::Published,
            'published_at' => now(),
        ]);

        return JobApplication::factory()->create(array_merge([
            'candidate_id' => $candidate->id,
            'job_posting_id' => $job->id,
            'candidate_resume_id' => $resume->id,
            'cover_letter' => 'I would like to join your team.',
            'status' => ApplicationStatus::Applied,
        ], $overrides));
    }

    public function test_employer_can_list_and_open_an_applicant(): void
    {
        $employer = Employer::factory()->create();
        $application = $this->applicationFor($employer);

        $this->actingAs($employer, 'employer')
            ->get('/employer/applicants')
            ->assertOk()
            ->assertSee('Applications received')
            ->assertSee('Abel Poole')
            ->assertSee('Frontend Web Developer')
            ->assertSee('Software Engineer')
            ->assertSee('abel@example.test')
            ->assertSee('Toronto, Ontario, Canada')
            ->assertSee('View resume & chat');

        $this->actingAs($employer, 'employer')
            ->get('/employer/applicants/'.$application->id)
            ->assertOk()
            ->assertSee('Abel Poole')
            ->assertSee('abel@example.test')
            ->assertSee('I would like to join your team.')
            ->assertSee('View resume');
    }

    public function test_applicants_index_shows_empty_state(): void
    {
        $employer = Employer::factory()->create();

        $this->actingAs($employer, 'employer')
            ->get('/employer/applicants')
            ->assertOk()
            ->assertSee('Applications received')
            ->assertSee('No applicants yet');
    }

    public function test_employer_can_view_the_submitted_resume(): void
    {
        $employer = Employer::factory()->create();
        $application = $this->applicationFor($employer);

        $this->actingAs($employer, 'employer')
            ->get('/employer/applicants/'.$application->id.'/resume')
            ->assertOk()
            ->assertSee('Voluptatum corporis')
            ->assertSee('Abel Poole')
            ->assertSee('Back to applicant')
            ->assertDontSee('All resumes');
    }

    public function test_employer_can_update_application_status(): void
    {
        $employer = Employer::factory()->create();
        $application = $this->applicationFor($employer);

        $this->actingAs($employer, 'employer')
            ->patch('/employer/applicants/'.$application->id.'/status', [
                'status' => ApplicationStatus::Shortlisted->value,
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertSame(ApplicationStatus::Shortlisted, $application->fresh()->status);
    }

    public function test_employer_cannot_view_another_employers_applicant(): void
    {
        $owner = Employer::factory()->create();
        $intruder = Employer::factory()->create();
        $application = $this->applicationFor($owner);

        $this->actingAs($intruder, 'employer')
            ->get('/employer/applicants/'.$application->id)
            ->assertNotFound();
    }
}
