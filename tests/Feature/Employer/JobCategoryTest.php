<?php

namespace Tests\Feature\Employer;

use App\Enums\CategoryStatus;
use App\Models\Category;
use App\Models\Employer;
use App\Models\JobPosting;
use App\Models\Plan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JobCategoryTest extends TestCase
{
    use RefreshDatabase;

    protected Employer $employer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->employer = Employer::factory()->create();
    }

    protected function activatePlan(): void
    {
        $plan = Plan::factory()->create([
            'jobs_allowed' => 5,
            'job_duration_value' => 30,
            'job_duration_unit' => 'day',
            'amount' => 0,
            'plan_type' => 'free',
        ]);

        $this->actingAs($this->employer, 'employer')
            ->post("/employer/plans/{$plan->id}/purchase")
            ->assertRedirect();
    }

    public function test_employer_can_post_job_with_approved_category(): void
    {
        $this->activatePlan();
        $category = Category::factory()->create(['name' => 'Engineering']);

        $this->actingAs($this->employer, 'employer')
            ->post('/employer/jobs', [
                'title' => 'Backend Engineer',
                'description' => 'API work',
                'currency' => 'INR',
                'status' => 'published',
                'category_selection' => 'listed',
                'category_id' => $category->id,
                'domain_ids' => [$this->employer->domains()->value('domains.id')],
            ])
            ->assertRedirect('/employer/jobs');

        $this->assertDatabaseHas('job_postings', [
            'title' => 'Backend Engineer',
            'category_id' => $category->id,
        ]);
    }

    public function test_other_category_matching_approved_name_shows_error(): void
    {
        $this->activatePlan();
        Category::factory()->create(['name' => 'Design']);

        $this->actingAs($this->employer, 'employer')
            ->post('/employer/jobs', [
                'title' => 'Designer',
                'description' => 'UI work',
                'currency' => 'INR',
                'status' => 'published',
                'category_selection' => 'other',
                'custom_category_name' => 'Design',
                'domain_ids' => [$this->employer->domains()->value('domains.id')],
            ])
            ->assertSessionHasErrors('custom_category_name');
    }

    public function test_other_category_creates_pending_suggestion(): void
    {
        $this->activatePlan();

        $this->actingAs($this->employer, 'employer')
            ->post('/employer/jobs', [
                'title' => 'Robotics Lead',
                'description' => 'Build robots',
                'currency' => 'INR',
                'status' => 'published',
                'category_selection' => 'other',
                'custom_category_name' => 'Robotics',
                'domain_ids' => [$this->employer->domains()->value('domains.id')],
            ])
            ->assertRedirect('/employer/jobs');

        $category = Category::query()->where('name', 'Robotics')->first();

        $this->assertNotNull($category);
        $this->assertSame(CategoryStatus::Pending, $category->status);
        $this->assertSame('robotics', $category->slug);
        $this->assertSame(1, $category->suggestion_count);
        $this->assertFalse($category->is_active);

        $this->assertDatabaseHas('job_postings', [
            'title' => 'Robotics Lead',
            'category_id' => $category->id,
        ]);
    }

    public function test_second_employer_reusing_pending_name_increments_count_without_error(): void
    {
        $first = Employer::factory()->create();
        $pending = Category::factory()->pending($first)->create([
            'name' => 'Green Energy',
            'slug' => 'green-energy',
            'suggestion_count' => 1,
        ]);

        $this->activatePlan();

        $this->actingAs($this->employer, 'employer')
            ->from('/employer/jobs/create')
            ->post('/employer/jobs', [
                'title' => 'Solar Engineer',
                'description' => 'Panels',
                'currency' => 'INR',
                'status' => 'published',
                'category_selection' => 'other',
                'custom_category_name' => 'green energy',
                'domain_ids' => [$this->employer->domains()->value('domains.id')],
            ])
            ->assertRedirect('/employer/jobs')
            ->assertSessionHasNoErrors();

        $this->assertSame(2, $pending->fresh()->suggestion_count);
        $this->assertSame(1, Category::query()->matchingName('Green Energy')->count());
        $this->assertSame($pending->id, JobPosting::first()->category_id);
    }

    public function test_category_check_endpoint_reports_approved_match(): void
    {
        Category::factory()->create(['name' => 'Marketing']);

        $this->actingAs($this->employer, 'employer')
            ->getJson('/employer/categories/check?name=Marketing')
            ->assertOk()
            ->assertJson([
                'status' => 'exists_approved',
            ]);
    }

    public function test_pending_categories_are_hidden_from_job_create_listing(): void
    {
        $this->activatePlan();
        Category::factory()->create(['name' => 'Sales']);
        Category::factory()->pending()->create(['name' => 'Hidden Niche']);

        $this->actingAs($this->employer, 'employer')
            ->get('/employer/jobs/create')
            ->assertOk()
            ->assertSee('Sales')
            ->assertSee('Other')
            ->assertDontSee('Hidden Niche');
    }
}
