<?php

namespace Tests\Feature\Admin;

use App\Enums\AccountStatus;
use App\Enums\ActivityAction;
use App\Models\ActivityLog;
use App\Models\Admin;
use App\Models\AdminRole;
use App\Models\Category;
use App\Models\Domain;
use App\Models\Employer;
use App\Models\JobPosting;
use App\Models\Plan;
use App\Models\Setting;
use Database\Seeders\AdminAccessSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ActivityLogTest extends TestCase
{
    use RefreshDatabase;

    protected Admin $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(AdminAccessSeeder::class);
        $this->admin = Admin::factory()->superAdmin()->create();
    }

    public function test_guests_cannot_view_activity_log(): void
    {
        $this->get('/admin/activity')->assertRedirect('/admin/login');
    }

    public function test_moderator_without_permission_cannot_view_activity_log(): void
    {
        $role = AdminRole::query()->where('slug', 'candidate-moderator')->firstOrFail();
        $moderator = Admin::factory()->moderator($role)->create();

        $this->actingAs($moderator, 'admin')
            ->get('/admin/activity')
            ->assertForbidden();
    }

    public function test_admin_can_browse_and_view_activity_details(): void
    {
        $log = ActivityLog::factory()->create([
            'actor_name' => 'Audit Admin',
            'description' => 'Settings changed for branding',
            'action' => ActivityAction::SettingsUpdated,
            'old_values' => ['site_name' => 'Old Brand'],
            'new_values' => ['site_name' => 'New Brand'],
        ]);

        $this->actingAs($this->admin, 'admin')
            ->get('/admin/activity')
            ->assertOk()
            ->assertSee('Audit Admin')
            ->assertSee('Settings changed for branding');

        $this->actingAs($this->admin, 'admin')
            ->get('/admin/activity/'.$log->id)
            ->assertOk()
            ->assertSee('Old Brand')
            ->assertSee('New Brand');
    }

    public function test_suspending_an_employer_is_audited(): void
    {
        $employer = Employer::factory()->create(['company_name' => 'Acme Staffing']);

        $this->actingAs($this->admin, 'admin')
            ->patch('/admin/employers/'.$employer->id.'/status', [
                'status' => AccountStatus::Suspended->value,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('activity_logs', [
            'action' => ActivityAction::EmployerSuspended->value,
            'actor_id' => $this->admin->id,
            'actor_guard' => 'admin',
            'subject_id' => $employer->id,
        ]);

        $log = ActivityLog::query()->where('action', ActivityAction::EmployerSuspended)->first();

        $this->assertSame('active', $log->old_values['status']);
        $this->assertSame('suspended', $log->new_values['status']);
    }

    public function test_plan_create_and_discount_are_audited(): void
    {
        $domainId = Domain::query()->value('id');

        $this->actingAs($this->admin, 'admin')
            ->post('/admin/plans', [
                'title' => 'Audited Plan',
                'plan_type' => 'premium',
                'description' => 'Tracked plan',
                'duration_value' => 1,
                'duration_unit' => 'month',
                'currency' => 'INR',
                'amount' => '1000.00',
                'discount_amount' => '800.00',
                'jobs_allowed' => 3,
                'job_duration_value' => 30,
                'job_duration_unit' => 'day',
                'is_active' => '1',
                'domain_ids' => [$domainId],
            ])
            ->assertRedirect('/admin/plans');

        $plan = Plan::firstWhere('title', 'Audited Plan');

        $this->assertNotNull($plan);
        $this->assertDatabaseHas('activity_logs', [
            'action' => ActivityAction::PlanCreated->value,
            'subject_id' => $plan->id,
        ]);
        $this->assertDatabaseHas('activity_logs', [
            'action' => ActivityAction::DiscountApplied->value,
            'subject_id' => $plan->id,
        ]);
    }

    public function test_settings_update_is_audited_with_old_and_new_values(): void
    {
        $domain = Domain::query()->where('is_default', true)->firstOrFail();
        Setting::set('site_name', 'Before Name', 'general', 'text', $domain->id);

        $this->actingAs($this->admin, 'admin')
            ->put('/admin/domains/'.$domain->id.'/settings', [
                'site_name' => 'After Name',
                'site_tagline' => 'See your career clearly',
                'contact_email' => 'hello@careermirror.com',
                'default_currency' => 'INR',
                'maintenance_mode' => '0',
            ])
            ->assertRedirect();

        $log = ActivityLog::query()
            ->where('action', ActivityAction::SettingsUpdated)
            ->latest('id')
            ->first();

        $this->assertNotNull($log);
        $this->assertSame('Before Name', $log->old_values['site_name']);
        $this->assertSame('After Name', $log->new_values['site_name']);
    }

    public function test_employer_job_lifecycle_is_audited(): void
    {
        $employer = Employer::factory()->create();
        $plan = Plan::factory()->create([
            'jobs_allowed' => 2,
            'job_duration_value' => 14,
            'job_duration_unit' => 'day',
            'amount' => 0,
            'plan_type' => 'free',
        ]);

        $this->actingAs($employer, 'employer')
            ->post("/employer/plans/{$plan->id}/purchase")
            ->assertRedirect();

        $this->assertDatabaseHas('activity_logs', [
            'action' => ActivityAction::PlanPurchased->value,
            'actor_guard' => 'employer',
            'actor_id' => $employer->id,
        ]);

        $category = Category::factory()->create(['name' => 'Engineering']);

        $this->actingAs($employer, 'employer')
            ->post('/employer/jobs', [
                'title' => 'Audited Role',
                'description' => 'Build things',
                'location' => 'Remote',
                'currency' => 'INR',
                'status' => 'published',
                'category_selection' => 'listed',
                'category_id' => $category->id,
                'domain_ids' => [$employer->domains()->value('domains.id')],
            ])
            ->assertRedirect('/employer/jobs');

        $job = JobPosting::firstWhere('title', 'Audited Role');
        $this->assertNotNull($job);

        $this->assertDatabaseHas('activity_logs', [
            'action' => ActivityAction::JobCreated->value,
            'subject_id' => $job->id,
            'actor_guard' => 'employer',
        ]);

        $this->actingAs($employer, 'employer')
            ->put('/employer/jobs/'.$job->id, [
                'title' => 'Audited Role Updated',
                'description' => 'Build more things',
                'location' => 'Hybrid',
                'currency' => 'INR',
                'status' => 'published',
                'category_selection' => 'listed',
                'category_id' => $category->id,
                'domain_ids' => [$employer->domains()->value('domains.id')],
            ])
            ->assertRedirect('/employer/jobs');

        $this->assertDatabaseHas('activity_logs', [
            'action' => ActivityAction::JobUpdated->value,
            'subject_id' => $job->id,
        ]);

        $this->actingAs($employer, 'employer')
            ->delete('/employer/jobs/'.$job->id)
            ->assertRedirect('/employer/jobs');

        $this->assertDatabaseHas('activity_logs', [
            'action' => ActivityAction::JobDeleted->value,
            'actor_id' => $employer->id,
        ]);
    }

    public function test_moderator_changes_are_audited(): void
    {
        $role = AdminRole::query()->where('slug', 'finance-admin')->firstOrFail();

        $this->actingAs($this->admin, 'admin')
            ->post('/admin/moderators', [
                'name' => 'Pat Moderator',
                'email' => 'pat@example.com',
                'phone' => '9876543210',
                'password' => 'password',
                'password_confirmation' => 'password',
                'admin_role_id' => $role->id,
                'is_active' => '1',
            ])
            ->assertRedirect('/admin/moderators');

        $moderator = Admin::query()->where('email', 'pat@example.com')->first();
        $this->assertNotNull($moderator);

        $this->assertDatabaseHas('activity_logs', [
            'action' => ActivityAction::ModeratorCreated->value,
            'subject_id' => $moderator->id,
        ]);
    }
}
