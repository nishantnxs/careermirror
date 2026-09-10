<?php

namespace Tests\Feature\Admin;

use App\Enums\AccountStatus;
use App\Models\Admin;
use App\Models\Domain;
use App\Models\Employer;
use App\Models\Order;
use App\Models\Plan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmployerManagementTest extends TestCase
{
    use RefreshDatabase;

    protected Admin $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = Admin::factory()->create();
    }

    public function test_guests_cannot_view_employers(): void
    {
        $this->get('/admin/employers')->assertRedirect('/admin/login');
    }

    public function test_admin_can_view_and_search_employers(): void
    {
        Employer::factory()->create([
            'company_name' => 'Acme Staffing',
            'email' => 'alex@acme.test',
        ]);
        Employer::factory()->create([
            'company_name' => 'Beta Works',
            'email' => 'beta@example.com',
        ]);

        $this->actingAs($this->admin, 'admin')
            ->get('/admin/employers?search=Acme')
            ->assertOk()
            ->assertSee('Acme Staffing')
            ->assertDontSee('Beta Works');
    }

    public function test_admin_can_filter_employers_by_status(): void
    {
        Employer::factory()->create(['company_name' => 'Active Co']);
        Employer::factory()->suspended()->create(['company_name' => 'Paused Co']);

        $this->actingAs($this->admin, 'admin')
            ->get('/admin/employers?status=suspended')
            ->assertOk()
            ->assertSee('Paused Co')
            ->assertDontSee('Active Co');
    }

    public function test_admin_can_view_employer_details(): void
    {
        $employer = Employer::factory()->create([
            'company_name' => 'Acme Staffing',
            'name' => 'Alex Hiring',
        ]);

        $this->actingAs($this->admin, 'admin')
            ->get('/admin/employers/'.$employer->id)
            ->assertOk()
            ->assertSee('Acme Staffing')
            ->assertSee('Alex Hiring')
            ->assertSee('Current subscription')
            ->assertSee('Allowed domains')
            ->assertSee('Assign plan')
            ->assertSee('Job postings');
    }

    public function test_admin_can_assign_a_plan_to_an_employer_with_payment_details(): void
    {
        $employer = Employer::factory()->create(['company_name' => 'Acme Staffing']);
        $plan = Plan::factory()->create([
            'title' => 'Growth Plan',
            'amount' => 1999,
            'jobs_allowed' => 5,
            'duration_value' => 1,
            'duration_unit' => 'month',
        ]);

        $this->actingAs($this->admin, 'admin')
            ->post('/admin/employers/'.$employer->id.'/assign-plan', [
                'plan_id' => $plan->id,
                'payment_amount' => '1500.00',
                'payment_mode' => 'bank_transfer',
                'transaction_reference' => 'NEFT998877',
                'payment_notes' => 'Paid offline at office',
            ])
            ->assertRedirect('/admin/employers/'.$employer->id)
            ->assertSessionHas('success');

        $this->assertDatabaseHas('orders', [
            'employer_id' => $employer->id,
            'plan_id' => $plan->id,
            'final_amount' => 1500.00,
            'payment_mode' => 'bank_transfer',
            'transaction_reference' => 'NEFT998877',
            'payment_notes' => 'Paid offline at office',
            'payment_status' => 'paid',
        ]);

        $order = Order::query()->where('employer_id', $employer->id)->first();
        $this->assertNotNull($order);
        $this->assertTrue($order->payment_meta['assigned_by_admin'] ?? false);
        $this->assertSame($this->admin->id, $order->payment_meta['assigned_by_admin_id']);

        $this->assertDatabaseHas('employer_subscriptions', [
            'employer_id' => $employer->id,
            'plan_id' => $plan->id,
            'status' => 'active',
            'jobs_allowed' => 5,
        ]);

        $this->actingAs($this->admin, 'admin')
            ->get('/admin/employers/'.$employer->id)
            ->assertOk()
            ->assertSee('Growth Plan')
            ->assertSee('NEFT998877')
            ->assertSee('Bank transfer');
    }

    public function test_admin_can_assign_a_complimentary_plan_with_zero_amount(): void
    {
        $employer = Employer::factory()->create();
        $plan = Plan::factory()->create([
            'title' => 'Partner Plan',
            'amount' => 999,
            'jobs_allowed' => 3,
        ]);

        $this->actingAs($this->admin, 'admin')
            ->post('/admin/employers/'.$employer->id.'/assign-plan', [
                'plan_id' => $plan->id,
                'payment_amount' => '0',
                'payment_mode' => 'admin_assigned',
                'payment_notes' => 'Complimentary partner access',
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('orders', [
            'employer_id' => $employer->id,
            'plan_id' => $plan->id,
            'final_amount' => 0,
            'payment_mode' => 'free',
            'payment_notes' => 'Complimentary partner access',
        ]);
    }

    public function test_admin_plan_assignment_requires_plan_and_amount(): void
    {
        $employer = Employer::factory()->create();

        $this->actingAs($this->admin, 'admin')
            ->post('/admin/employers/'.$employer->id.'/assign-plan', [])
            ->assertSessionHasErrors(['plan_id', 'payment_amount', 'payment_mode']);
    }

    public function test_create_employer_page_renders(): void
    {
        $this->actingAs($this->admin, 'admin')
            ->get('/admin/employers/create')
            ->assertOk()
            ->assertSee('Add Employer');
    }

    public function test_admin_can_create_an_employer(): void
    {
        $domain = Domain::query()->where('is_default', true)->firstOrFail();

        $this->actingAs($this->admin, 'admin')
            ->post('/admin/employers', [
                'name' => 'Alex Hiring',
                'company_name' => 'Acme Staffing',
                'email' => 'alex@acme.test',
                'phone' => '9876543210',
                'password' => 'password',
                'password_confirmation' => 'password',
                'status' => 'active',
                'domain_ids' => [$domain->id],
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $employer = Employer::query()->where('email', 'alex@acme.test')->first();

        $this->assertNotNull($employer);
        $this->assertSame('Acme Staffing', $employer->company_name);
        $this->assertSame(AccountStatus::Active, $employer->status);
        $this->assertTrue(password_verify('password', $employer->password));
        $this->assertTrue($employer->domains()->whereKey($domain->id)->exists());
    }

    public function test_employer_creation_validates_required_fields(): void
    {
        $this->actingAs($this->admin, 'admin')
            ->post('/admin/employers', [])
            ->assertSessionHasErrors(['name', 'company_name', 'email', 'password', 'status', 'domain_ids']);
    }

    public function test_employer_creation_requires_a_unique_email(): void
    {
        Employer::factory()->create(['email' => 'alex@acme.test']);
        $domain = Domain::query()->where('is_default', true)->firstOrFail();

        $this->actingAs($this->admin, 'admin')
            ->post('/admin/employers', [
                'name' => 'Alex Two',
                'company_name' => 'Acme Two',
                'email' => 'alex@acme.test',
                'password' => 'password',
                'password_confirmation' => 'password',
                'status' => 'active',
                'domain_ids' => [$domain->id],
            ])
            ->assertSessionHasErrors('email');
    }

    public function test_admin_can_update_employer_allowed_domains(): void
    {
        $domainA = Domain::query()->where('is_default', true)->firstOrFail();
        $domainB = Domain::factory()->create([
            'host' => 'portal-b.test',
            'url' => 'https://portal-b.test',
        ]);

        $employer = Employer::factory()->create();
        $employer->domains()->sync([$domainA->id]);

        $this->actingAs($this->admin, 'admin')
            ->put('/admin/employers/'.$employer->id.'/domains', [
                'domain_ids' => [$domainA->id, $domainB->id],
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertEqualsCanonicalizing(
            [$domainA->id, $domainB->id],
            $employer->domains()->pluck('domains.id')->map(fn ($id) => (int) $id)->all(),
        );
    }

    public function test_admin_can_activate_deactivate_and_suspend_an_employer(): void
    {
        $employer = Employer::factory()->create();

        $this->actingAs($this->admin, 'admin')
            ->patch('/admin/employers/'.$employer->id.'/status', ['status' => 'inactive'])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertSame(AccountStatus::Inactive, $employer->fresh()->status);

        $this->actingAs($this->admin, 'admin')
            ->patch('/admin/employers/'.$employer->id.'/status', ['status' => 'suspended'])
            ->assertRedirect();

        $this->assertSame(AccountStatus::Suspended, $employer->fresh()->status);

        $this->actingAs($this->admin, 'admin')
            ->patch('/admin/employers/'.$employer->id.'/status', ['status' => 'active'])
            ->assertRedirect();

        $this->assertSame(AccountStatus::Active, $employer->fresh()->status);
    }
}
