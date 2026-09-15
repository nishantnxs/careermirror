<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Category;
use App\Models\Domain;
use App\Models\Employer;
use App\Models\JobPosting;
use App\Models\Plan;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MultiDomainFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_employers_see_all_plans_regardless_of_login_website(): void
    {
        $domainA = Domain::query()->where('is_default', true)->firstOrFail();
        $domainB = Domain::factory()->create(['host' => 'portal-b.test', 'url' => 'https://portal-b.test']);

        $planA = Plan::factory()->create(['title' => 'Plan A Only', 'amount' => 0, 'plan_type' => 'free']);
        $planB = Plan::factory()->create(['title' => 'Plan B Only', 'amount' => 0, 'plan_type' => 'free']);

        $planA->domains()->sync([$domainA->id]);
        $planB->domains()->sync([$domainB->id]);

        $employer = Employer::factory()->create();
        $employer->domains()->sync([$domainA->id]);

        $this->actingAs($employer, 'employer')
            ->get('/employer/plans')
            ->assertOk()
            ->assertSee('Plan A Only')
            ->assertSee('Plan B Only');

        $this->actingAs($employer, 'employer')
            ->get('/employer/plans/'.$planB->id.'/checkout')
            ->assertOk()
            ->assertSee('Plan B Only');
    }

    public function test_job_domains_sync_and_settings_are_isolated(): void
    {
        $domainA = Domain::query()->where('is_default', true)->firstOrFail();
        $domainB = Domain::factory()->create(['host' => 'portal-b.test', 'url' => 'https://portal-b.test']);

        Setting::set('site_name', 'Site A', 'general', 'text', $domainA->id);
        Setting::set('site_name', 'Site B', 'general', 'text', $domainB->id);

        $this->assertSame('Site A', Setting::get('site_name', null, $domainA->id));
        $this->assertSame('Site B', Setting::get('site_name', null, $domainB->id));

        $employer = Employer::factory()->create();
        $employer->domains()->sync([$domainA->id, $domainB->id]);

        $plan = Plan::factory()->create([
            'jobs_allowed' => 2,
            'amount' => 0,
            'plan_type' => 'free',
        ]);
        $plan->domains()->sync([$domainA->id]);

        $this->actingAs($employer, 'employer')
            ->post("/employer/plans/{$plan->id}/purchase")
            ->assertRedirect();

        $category = Category::factory()->create(['name' => 'Engineering']);

        $this->actingAs($employer, 'employer')
            ->post('/employer/jobs', [
                'title' => 'Shared Job',
                'description' => 'Visible on both',
                'currency' => 'INR',
                'status' => 'published',
                'category_selection' => 'listed',
                'category_id' => $category->id,
                'domain_ids' => [$domainA->id, $domainB->id],
            ])
            ->assertRedirect('/employer/jobs');

        $job = JobPosting::firstWhere('title', 'Shared Job');

        $this->assertNotNull($job);
        $this->assertEqualsCanonicalizing(
            [$domainA->id, $domainB->id],
            $job->domains()->pluck('domains.id')->map(fn ($id) => (int) $id)->all()
        );

        $this->assertTrue(JobPosting::query()->forDomain($domainA)->whereKey($job->id)->exists());
        $this->assertTrue(JobPosting::query()->forDomain($domainB)->whereKey($job->id)->exists());
    }

    public function test_employer_can_post_a_job_to_any_active_domain(): void
    {
        $domainA = Domain::query()->where('is_default', true)->firstOrFail();
        $domainB = Domain::factory()->create(['host' => 'portal-b.test', 'url' => 'https://portal-b.test']);

        $employer = Employer::factory()->create();
        $employer->domains()->sync([$domainA->id]);

        $plan = Plan::factory()->create(['jobs_allowed' => 1, 'amount' => 0, 'plan_type' => 'free']);
        $plan->domains()->sync([$domainA->id]);

        $this->actingAs($employer, 'employer')
            ->post("/employer/plans/{$plan->id}/purchase");

        $category = Category::factory()->create();

        $this->actingAs($employer, 'employer')
            ->post('/employer/jobs', [
                'title' => 'Cross Domain Job',
                'description' => 'Posted to another website',
                'currency' => 'INR',
                'status' => 'published',
                'category_selection' => 'listed',
                'category_id' => $category->id,
                'domain_ids' => [$domainB->id],
            ])
            ->assertRedirect('/employer/jobs');

        $job = JobPosting::firstWhere('title', 'Cross Domain Job');

        $this->assertNotNull($job);
        $this->assertTrue($job->domains()->whereKey($domainB->id)->exists());
    }

    public function test_employer_cannot_post_a_job_to_an_inactive_domain(): void
    {
        $domainA = Domain::query()->where('is_default', true)->firstOrFail();
        $inactive = Domain::factory()->inactive()->create([
            'host' => 'inactive.test',
            'url' => 'https://inactive.test',
        ]);

        $employer = Employer::factory()->create();

        $plan = Plan::factory()->create(['jobs_allowed' => 1, 'amount' => 0, 'plan_type' => 'free']);
        $plan->domains()->sync([$domainA->id]);

        $this->actingAs($employer, 'employer')
            ->post("/employer/plans/{$plan->id}/purchase");

        $category = Category::factory()->create();

        $this->actingAs($employer, 'employer')
            ->post('/employer/jobs', [
                'title' => 'Inactive Domain Job',
                'description' => 'Should fail',
                'currency' => 'INR',
                'status' => 'published',
                'category_selection' => 'listed',
                'category_id' => $category->id,
                'domain_ids' => [$inactive->id],
            ])
            ->assertSessionHasErrors('domain_ids');
    }

    public function test_job_form_lists_all_active_domains(): void
    {
        $domainA = Domain::query()->where('is_default', true)->firstOrFail();
        $domainB = Domain::factory()->create([
            'host' => 'portal-b.test',
            'url' => 'https://portal-b.test',
            'website_name' => 'Portal B',
        ]);
        Domain::factory()->create([
            'host' => 'portal-c.test',
            'url' => 'https://portal-c.test',
            'website_name' => 'Portal C',
        ]);

        $employer = Employer::factory()->create();
        $employer->domains()->sync([$domainA->id]);

        $plan = Plan::factory()->create(['jobs_allowed' => 1, 'amount' => 0, 'plan_type' => 'free']);
        $plan->domains()->sync([$domainA->id]);

        $this->actingAs($employer, 'employer')
            ->post("/employer/plans/{$plan->id}/purchase");

        $this->actingAs($employer, 'employer')
            ->get('/employer/jobs/create')
            ->assertOk()
            ->assertSee($domainA->host)
            ->assertSee('portal-b.test')
            ->assertSee('portal-c.test');
    }

    public function test_new_domain_can_be_granted_to_all_employers(): void
    {
        $employer = Employer::factory()->create();
        $admin = Admin::factory()->create();

        $this->actingAs($admin, 'admin')
            ->post('/admin/domains', [
                'name' => 'Portal B',
                'host' => 'portal-b.test',
                'url' => 'https://portal-b.test',
                'website_name' => 'Portal B',
                'status' => 'active',
                'is_default' => '0',
                'grant_all_employers' => '1',
            ])
            ->assertRedirect();

        $domain = Domain::query()->where('host', 'portal-b.test')->first();

        $this->assertNotNull($domain);
        $this->assertTrue($employer->domains()->whereKey($domain->id)->exists());
    }

    public function test_employer_registration_grants_all_active_domains(): void
    {
        $domainA = Domain::query()->where('is_default', true)->firstOrFail();
        $domainB = Domain::factory()->create([
            'host' => 'portal-b.test',
            'url' => 'https://portal-b.test',
            'status' => 'active',
        ]);

        $this->post('/employer/register', [
            'name' => 'Alex Hiring',
            'company_name' => 'Acme Staffing',
            'email' => 'alex@acme.test',
            'phone' => '9876543210',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertRedirect('/employer');

        $employer = Employer::query()->where('email', 'alex@acme.test')->first();

        $this->assertNotNull($employer);
        $this->assertEqualsCanonicalizing(
            [$domainA->id, $domainB->id],
            $employer->domains()->pluck('domains.id')->map(fn ($id) => (int) $id)->all()
        );
    }

    public function test_updating_a_domain_can_grant_it_to_existing_employers(): void
    {
        $employer = Employer::factory()->create();
        $admin = Admin::factory()->create();
        $domain = Domain::factory()->create([
            'host' => 'portal-b.test',
            'url' => 'https://portal-b.test',
            'name' => 'Portal B',
            'website_name' => 'Portal B',
            'status' => 'active',
        ]);

        $this->assertFalse($employer->domains()->whereKey($domain->id)->exists());

        $this->actingAs($admin, 'admin')
            ->put('/admin/domains/'.$domain->id, [
                'name' => $domain->name,
                'host' => $domain->host,
                'url' => $domain->url,
                'website_name' => $domain->website_name,
                'status' => 'active',
                'is_default' => '0',
                'grant_all_employers' => '1',
            ])
            ->assertRedirect();

        $this->assertTrue($employer->fresh()->domains()->whereKey($domain->id)->exists());
    }
}
