<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use App\Models\Domain;
use App\Models\Plan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlanModuleTest extends TestCase
{
    use RefreshDatabase;

    protected Admin $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = Admin::factory()->create();
    }

    /** @return array<string, mixed> */
    protected function validPayload(array $overrides = []): array
    {
        return array_merge([
            'title' => 'Premium Yearly',
            'plan_type' => 'premium',
            'description' => 'Everything unlocked.',
            'duration_value' => 1,
            'duration_unit' => 'year',
            'currency' => 'INR',
            'amount' => '4999.00',
            'discount_amount' => '3999.00',
            'trial_days' => 14,
            'jobs_allowed' => 5,
            'job_duration_value' => 30,
            'job_duration_unit' => 'day',
            'features' => ['Unlimited reviews', '', 'Career coaching'],
            'expiry_date' => now()->addMonth()->toDateString(),
            'is_featured' => '1',
            'is_active' => '1',
            'sort_order' => 3,
            'domain_ids' => [Domain::query()->value('id')],
        ], $overrides);
    }

    public function test_plan_index_and_create_pages_render(): void
    {
        Plan::factory()->count(3)->create();

        $this->actingAs($this->admin, 'admin')->get('/admin/plans')->assertOk();
        $this->actingAs($this->admin, 'admin')->get('/admin/plans/create')->assertOk();
    }

    public function test_admin_can_create_a_plan(): void
    {
        $this->actingAs($this->admin, 'admin')
            ->post('/admin/plans', $this->validPayload())
            ->assertRedirect('/admin/plans')
            ->assertSessionHas('success');

        $plan = Plan::firstWhere('title', 'Premium Yearly');

        $this->assertNotNull($plan);
        $this->assertSame('premium-yearly', $plan->slug);
        $this->assertSame('premium', $plan->plan_type->value);
        $this->assertSame('year', $plan->duration_unit->value);
        $this->assertSame(3999.00, $plan->payable_amount);
        $this->assertSame(20, $plan->discount_percent);
        $this->assertTrue($plan->is_featured);
        // Blank feature rows are stripped before saving.
        $this->assertSame(['Unlimited reviews', 'Career coaching'], $plan->features);
    }

    public function test_plan_creation_validates_required_fields(): void
    {
        $this->actingAs($this->admin, 'admin')
            ->post('/admin/plans', [])
            ->assertSessionHasErrors(['title', 'plan_type', 'duration_unit', 'currency', 'amount']);
    }

    public function test_discount_must_be_lower_than_amount(): void
    {
        $this->actingAs($this->admin, 'admin')
            ->post('/admin/plans', $this->validPayload([
                'amount' => '500',
                'discount_amount' => '600',
            ]))
            ->assertSessionHasErrors('discount_amount');
    }

    public function test_expiry_date_cannot_be_in_the_past(): void
    {
        $this->actingAs($this->admin, 'admin')
            ->post('/admin/plans', $this->validPayload([
                'expiry_date' => now()->subWeek()->toDateString(),
            ]))
            ->assertSessionHasErrors('expiry_date');
    }

    public function test_lifetime_plans_do_not_require_a_duration_value(): void
    {
        $payload = $this->validPayload(['duration_unit' => 'lifetime']);
        unset($payload['duration_value']);

        $this->actingAs($this->admin, 'admin')
            ->post('/admin/plans', $payload)
            ->assertSessionHasNoErrors();

        $plan = Plan::firstWhere('title', 'Premium Yearly');

        $this->assertSame('lifetime', $plan->duration_unit->value);
        $this->assertSame('Lifetime', $plan->duration_label);
        $this->assertNull($plan->duration_in_days);
    }

    public function test_slug_must_be_unique(): void
    {
        Plan::factory()->create(['slug' => 'premium-yearly']);

        $this->actingAs($this->admin, 'admin')
            ->post('/admin/plans', $this->validPayload(['slug' => 'premium-yearly']))
            ->assertSessionHasErrors('slug');
    }

    public function test_admin_can_update_a_plan(): void
    {
        $plan = Plan::factory()->create(['title' => 'Old Title']);

        $this->actingAs($this->admin, 'admin')
            ->put("/admin/plans/{$plan->id}", $this->validPayload([
                'title' => 'New Title',
                'slug' => $plan->slug,
            ]))
            ->assertRedirect('/admin/plans');

        $this->assertSame('New Title', $plan->fresh()->title);
    }

    public function test_admin_can_view_edit_and_detail_pages(): void
    {
        $plan = Plan::factory()->create();

        $this->actingAs($this->admin, 'admin')->get("/admin/plans/{$plan->id}")->assertOk();
        $this->actingAs($this->admin, 'admin')->get("/admin/plans/{$plan->id}/edit")->assertOk();
    }

    public function test_admin_can_toggle_plan_status(): void
    {
        $plan = Plan::factory()->create(['is_active' => true]);

        $this->actingAs($this->admin, 'admin')
            ->patch("/admin/plans/{$plan->id}/toggle-status")
            ->assertRedirect();

        $this->assertFalse($plan->fresh()->is_active);
    }

    public function test_admin_can_delete_a_plan(): void
    {
        $plan = Plan::factory()->create();

        $this->actingAs($this->admin, 'admin')
            ->delete("/admin/plans/{$plan->id}")
            ->assertRedirect('/admin/plans');

        $this->assertSoftDeleted($plan);
    }

    public function test_available_scope_excludes_inactive_and_expired_plans(): void
    {
        Plan::factory()->create(['title' => 'Live Plan']);
        Plan::factory()->inactive()->create();
        Plan::factory()->expired()->create();

        $available = Plan::available()->pluck('title');

        $this->assertCount(1, $available);
        $this->assertSame('Live Plan', $available->first());
    }
}
