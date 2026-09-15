<?php

namespace Tests\Feature\Employer;

use App\Enums\JobStatus;
use App\Enums\PlanType;
use App\Enums\SubscriptionStatus;
use App\Models\Category;
use App\Models\Domain;
use App\Models\Employer;
use App\Models\EmployerSubscription;
use App\Models\JobPosting;
use App\Models\Order;
use App\Models\Plan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JobPostingTest extends TestCase
{
    use RefreshDatabase;

    protected Employer $employer;

    protected Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->employer = Employer::factory()->create();
        $this->category = Category::factory()->create(['name' => 'Engineering']);
    }

    protected function activatePlan(int $jobsAllowed = 2, ?int $jobDurationDays = 30): EmployerSubscription
    {
        $plan = Plan::factory()->create([
            'jobs_allowed' => $jobsAllowed,
            'job_duration_value' => $jobDurationDays ?? 30,
            'job_duration_unit' => 'day',
            'amount' => 0,
            'plan_type' => 'free',
        ]);

        $this->actingAs($this->employer, 'employer')
            ->post("/employer/plans/{$plan->id}/purchase")
            ->assertRedirect();

        return EmployerSubscription::query()
            ->where('employer_id', $this->employer->id)
            ->active()
            ->firstOrFail();
    }

    /** @return array<string, mixed> */
    protected function jobPayload(array $overrides = []): array
    {
        return array_merge([
            'title' => 'Laravel Developer',
            'description' => 'Build and ship features.',
            'location' => 'Remote',
            'job_type' => 'full-time',
            'experience_level' => 'mid',
            'salary_min' => 50000,
            'salary_max' => 80000,
            'currency' => 'INR',
            'status' => 'published',
            'category_selection' => 'listed',
            'category_id' => $this->category->id,
            'domain_ids' => [$this->employer->domains()->value('domains.id') ?? Domain::query()->value('id')],
        ], $overrides);
    }

    public function test_employer_without_subscription_cannot_open_job_create_page(): void
    {
        $this->actingAs($this->employer, 'employer')
            ->get('/employer/jobs/create')
            ->assertRedirect('/employer/plans')
            ->assertSessionHas('error');
    }

    public function test_employer_with_active_plan_can_post_a_job(): void
    {
        $subscription = $this->activatePlan(2, 14);

        $this->actingAs($this->employer, 'employer')
            ->post('/employer/jobs', $this->jobPayload())
            ->assertRedirect('/employer/jobs')
            ->assertSessionHas('success');

        $job = JobPosting::first();

        $this->assertNotNull($job);
        $this->assertSame('Laravel Developer', $job->title);
        $this->assertSame(JobStatus::Published, $job->status);
        $this->assertSame($subscription->id, $job->employer_subscription_id);
        $this->assertSame($this->category->id, $job->category_id);
        $this->assertTrue($job->expires_at->isAfter(now()->addDays(13)));

        $this->assertSame(1, $subscription->fresh()->jobs_used);
    }

    public function test_employer_cannot_post_when_job_credits_are_exhausted(): void
    {
        $subscription = $this->activatePlan(1);

        $this->actingAs($this->employer, 'employer')
            ->post('/employer/jobs', $this->jobPayload([
                'title' => 'First Job',
                'description' => 'Uses the only credit.',
            ]))
            ->assertRedirect('/employer/jobs');

        $this->actingAs($this->employer, 'employer')
            ->get('/employer/jobs/create')
            ->assertRedirect('/employer/plans');

        $this->actingAs($this->employer, 'employer')
            ->post('/employer/jobs', $this->jobPayload([
                'title' => 'Second Job',
                'description' => 'Should be blocked.',
            ]))
            ->assertRedirect('/employer/plans');

        $this->assertSame(1, JobPosting::count());
        $this->assertSame(1, $subscription->fresh()->jobs_used);
    }

    public function test_employer_can_post_one_job_per_purchased_plan_credit(): void
    {
        config(['payments.driver' => 'fake']);

        $free = Plan::factory()->free()->create(['jobs_allowed' => 1]);
        $starter = Plan::factory()->create([
            'jobs_allowed' => 1,
            'amount' => 399,
            'plan_type' => PlanType::Basic,
        ]);
        $quarterly = Plan::factory()->create([
            'jobs_allowed' => 1,
            'amount' => 1299,
            'plan_type' => PlanType::Standard,
        ]);

        $this->actingAs($this->employer, 'employer')
            ->post("/employer/plans/{$free->id}/purchase")
            ->assertRedirect();

        $this->actingAs($this->employer, 'employer')
            ->post('/employer/jobs', $this->jobPayload(['title' => 'Free plan job']))
            ->assertRedirect('/employer/jobs');

        foreach ([$starter, $quarterly] as $plan) {
            $start = $this->actingAs($this->employer, 'employer')
                ->post("/employer/plans/{$plan->id}/purchase", [
                    'payment_mode' => 'upi',
                ]);

            $order = Order::query()->where('plan_id', $plan->id)->latest('id')->firstOrFail();
            $start->assertRedirect(route('employer.payments.fake.complete', $order));

            $this->actingAs($this->employer, 'employer')
                ->get(route('employer.payments.fake.complete', $order))
                ->assertRedirect();
        }

        $this->actingAs($this->employer, 'employer')
            ->post('/employer/jobs', $this->jobPayload(['title' => 'Starter plan job']))
            ->assertRedirect('/employer/jobs');

        $this->actingAs($this->employer, 'employer')
            ->post('/employer/jobs', $this->jobPayload(['title' => 'Quarterly plan job']))
            ->assertRedirect('/employer/jobs');

        $this->actingAs($this->employer, 'employer')
            ->get('/employer/jobs/create')
            ->assertRedirect('/employer/plans');

        $this->assertSame(3, JobPosting::count());
        $this->assertSame(2, EmployerSubscription::query()->where('status', 'active')->value('jobs_used'));
    }

    public function test_employer_can_update_and_delete_own_job(): void
    {
        $this->activatePlan();

        $this->actingAs($this->employer, 'employer')
            ->post('/employer/jobs', $this->jobPayload([
                'title' => 'Original Title',
                'description' => 'Original description.',
            ]));

        $job = JobPosting::firstOrFail();

        $this->actingAs($this->employer, 'employer')
            ->put("/employer/jobs/{$job->id}", $this->jobPayload([
                'title' => 'Updated Title',
                'description' => 'Updated description.',
                'status' => 'paused',
            ]))
            ->assertRedirect('/employer/jobs');

        $this->assertSame('Updated Title', $job->fresh()->title);
        $this->assertSame(JobStatus::Paused, $job->fresh()->status);

        $this->actingAs($this->employer, 'employer')
            ->delete("/employer/jobs/{$job->id}")
            ->assertRedirect('/employer/jobs');

        $this->assertDatabaseMissing('job_postings', ['id' => $job->id]);
    }

    public function test_employer_cannot_edit_another_employers_job(): void
    {
        $other = Employer::factory()->create();
        $plan = Plan::factory()->create();
        $order = Order::factory()->create([
            'employer_id' => $other->id,
            'plan_id' => $plan->id,
        ]);
        $subscription = EmployerSubscription::factory()->create([
            'employer_id' => $other->id,
            'order_id' => $order->id,
            'plan_id' => $plan->id,
            'status' => SubscriptionStatus::Active,
        ]);
        $job = JobPosting::factory()->create([
            'employer_id' => $other->id,
            'employer_subscription_id' => $subscription->id,
        ]);

        $this->actingAs($this->employer, 'employer')
            ->get("/employer/jobs/{$job->id}/edit")
            ->assertNotFound();
    }
}
