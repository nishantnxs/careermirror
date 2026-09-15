<?php

namespace Tests\Feature\Employer;

use App\Enums\ApplicationStatus;
use App\Enums\JobStatus;
use App\Enums\PlanType;
use App\Enums\SubscriptionStatus;
use App\Models\Candidate;
use App\Models\Employer;
use App\Models\EmployerSubscription;
use App\Models\JobApplication;
use App\Models\JobPosting;
use App\Models\Plan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_from_the_dashboard(): void
    {
        $this->get('/employer')->assertRedirect('/employer/login');
    }

    public function test_dashboard_renders_design_layout_and_empty_state(): void
    {
        $employer = Employer::factory()->create([
            'name' => 'Alex Hiring',
            'company_name' => 'Acme Staffing',
        ]);

        $this->actingAs($employer, 'employer')
            ->get('/employer')
            ->assertOk()
            ->assertSee('Welcome back, Alex')
            ->assertSee("Here's how your hiring is going.", false)
            ->assertSee('Job postings')
            ->assertSee('Applications received')
            ->assertSee('Current plan')
            ->assertSee('None')
            ->assertSee('Post a job')
            ->assertSee('Review applications')
            ->assertSee('Change plan')
            ->assertSee('Your job postings')
            ->assertSee('No job postings yet')
            ->assertDontSee('Active jobs')
            ->assertDontSee('Recent applicants');
    }

    public function test_dashboard_shows_stats_plan_and_job_postings(): void
    {
        $employer = Employer::factory()->create(['name' => 'Sam Employer']);
        $candidate = Candidate::factory()->create(['name' => 'Jane Applicant']);
        $plan = Plan::factory()->create([
            'title' => 'Professional',
            'plan_type' => PlanType::Premium,
        ]);

        EmployerSubscription::factory()->create([
            'employer_id' => $employer->id,
            'plan_id' => $plan->id,
            'status' => SubscriptionStatus::Active,
        ]);

        $job = JobPosting::factory()->for($employer)->create([
            'title' => 'Product Designer',
            'location' => 'Remote',
            'salary_min' => 80000,
            'salary_max' => 110000,
            'currency' => 'USD',
            'status' => JobStatus::Published,
            'published_at' => now()->subDays(3),
        ]);

        JobApplication::factory()->create([
            'candidate_id' => $candidate->id,
            'job_posting_id' => $job->id,
            'status' => ApplicationStatus::Applied,
        ]);

        $this->actingAs($employer, 'employer')
            ->get('/employer')
            ->assertOk()
            ->assertSee('Welcome back, Sam')
            ->assertSee('Professional')
            ->assertSee('Product Designer')
            ->assertSee('Remote')
            ->assertSee('published')
            ->assertSee('1 applicant')
            ->assertSee('View')
            ->assertDontSee('Jane Applicant')
            ->assertDontSee('No job postings yet');
    }
}
