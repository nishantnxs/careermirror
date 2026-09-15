<?php

namespace Tests\Feature\Candidate;

use App\Enums\ApplicationStatus;
use App\Models\Candidate;
use App\Models\CandidateResume;
use App\Models\Employer;
use App\Models\JobApplication;
use App\Models\JobPosting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_from_the_dashboard(): void
    {
        $this->get('/candidate')->assertRedirect('/candidate/login');
    }

    public function test_dashboard_renders_design_layout_and_empty_state(): void
    {
        $candidate = Candidate::factory()->create(['name' => 'Virender Singh']);

        $this->actingAs($candidate, 'candidate')
            ->get('/candidate')
            ->assertOk()
            ->assertSee('Welcome back, Virender')
            ->assertSee("Here's your job search at a glance.", false)
            ->assertSee('All applications')
            ->assertSee('Browse jobs')
            ->assertSee('Create resume')
            ->assertSee(route('candidate.resumes.create'), false)
            ->assertSee('Recent applications')
            ->assertSee('No applications yet')
            ->assertDontSee('Applications sent')
            ->assertDontSee('Edit resume')
            ->assertDontSee('My resumes');
    }

    public function test_dashboard_links_to_my_resumes_when_a_resume_exists(): void
    {
        $candidate = Candidate::factory()->create();
        CandidateResume::factory()->for($candidate)->create();

        $this->actingAs($candidate, 'candidate')
            ->get('/candidate')
            ->assertOk()
            ->assertSee('My resumes')
            ->assertSee(route('candidate.resumes.index'), false)
            ->assertDontSee('Create resume');
    }

    public function test_dashboard_shows_recent_applications_and_opens_them(): void
    {
        $candidate = Candidate::factory()->create(['name' => 'Jane Candidate']);
        $resume = CandidateResume::factory()->for($candidate)->default()->create();
        $employer = Employer::factory()->create(['company_name' => 'GWSHARE Technologies']);
        $job = JobPosting::factory()->for($employer)->create([
            'title' => 'Frontend Engineer',
            'location' => 'Toronto, ON',
        ]);

        $application = JobApplication::factory()->create([
            'candidate_id' => $candidate->id,
            'job_posting_id' => $job->id,
            'candidate_resume_id' => $resume->id,
            'status' => ApplicationStatus::Applied,
        ]);

        $this->actingAs($candidate, 'candidate')
            ->get('/candidate')
            ->assertOk()
            ->assertSee('Welcome back, Jane')
            ->assertSee('Frontend Engineer')
            ->assertSee('GWSHARE Technologies')
            ->assertSee('Toronto, ON')
            ->assertSee('submitted')
            ->assertSee(route('candidate.applications.show', $application), false)
            ->assertDontSee('No applications yet');
    }
}
