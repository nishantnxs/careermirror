<?php

namespace Tests\Feature\Employer;

use App\Enums\PaymentMode;
use App\Enums\PaymentStatus;
use App\Enums\PlanType;
use App\Enums\SubscriptionStatus;
use App\Models\Employer;
use App\Models\EmployerSubscription;
use App\Models\Order;
use App\Models\Plan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlanPurchaseTest extends TestCase
{
    use RefreshDatabase;

    protected Employer $employer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->employer = Employer::factory()->create();
        config(['payments.driver' => 'fake']);
    }

    public function test_employer_can_view_available_plans_including_free(): void
    {
        Plan::factory()->free()->create(['title' => 'Starter Free']);
        Plan::factory()->create(['title' => 'Growth Paid', 'amount' => 999, 'plan_type' => PlanType::Basic]);
        Plan::factory()->inactive()->create(['title' => 'Hidden']);

        $this->actingAs($this->employer, 'employer')
            ->get('/employer/plans')
            ->assertOk()
            ->assertSee('Plans that grow with your hiring')
            ->assertSee('Starter Free')
            ->assertSee('Growth Paid')
            ->assertSee('Start free')
            ->assertDontSee('Hidden');
    }

    public function test_plans_page_shows_original_and_discounted_prices(): void
    {
        Plan::factory()->create([
            'title' => 'Professional Quarterly',
            'amount' => 1299,
            'discount_amount' => 1199,
            'plan_type' => PlanType::Basic,
        ]);

        $this->actingAs($this->employer, 'employer')
            ->get('/employer/plans')
            ->assertOk()
            ->assertSee('Professional Quarterly')
            ->assertSee('₹1,199')
            ->assertSee('₹1,299')
            ->assertSee('Save 8%')
            ->assertSee('plan-price-original', false);
    }

    public function test_plans_page_hides_compare_price_when_there_is_no_discount(): void
    {
        Plan::factory()->create([
            'title' => 'Growth Paid',
            'amount' => 999,
            'discount_amount' => null,
            'plan_type' => PlanType::Basic,
        ]);

        $this->actingAs($this->employer, 'employer')
            ->get('/employer/plans')
            ->assertOk()
            ->assertSee('₹999')
            ->assertDontSee('plan-price-original', false)
            ->assertDontSee('plan-save-badge', false);
    }

    public function test_checkout_shows_original_and_discounted_prices(): void
    {
        $plan = Plan::factory()->create([
            'title' => 'Professional Quarterly',
            'amount' => 1299,
            'discount_amount' => 1199,
            'plan_type' => PlanType::Basic,
        ]);

        $this->actingAs($this->employer, 'employer')
            ->get("/employer/plans/{$plan->id}/checkout")
            ->assertOk()
            ->assertSee('Original price')
            ->assertSee('₹1,299.00')
            ->assertSee('₹1,199.00')
            ->assertSee('Save 8%');
    }

    public function test_checkout_shows_gateway_payment_methods(): void
    {
        $plan = Plan::factory()->create([
            'amount' => 500,
            'plan_type' => PlanType::Basic,
        ]);

        $this->actingAs($this->employer, 'employer')
            ->get("/employer/plans/{$plan->id}/checkout")
            ->assertOk()
            ->assertSee('PayPal')
            ->assertSee('Stripe')
            ->assertSee('Razorpay')
            ->assertSee('UPI')
            ->assertDontSee('Transaction / UTR');
    }

    public function test_employer_can_activate_a_free_plan_with_payment_audit_trail(): void
    {
        $plan = Plan::factory()->free()->create([
            'title' => 'Free Starter',
            'jobs_allowed' => 2,
            'duration_value' => 1,
            'duration_unit' => 'month',
            'job_duration_value' => 14,
            'job_duration_unit' => 'day',
        ]);

        $this->actingAs($this->employer, 'employer')
            ->post("/employer/plans/{$plan->id}/purchase", [
                'payment_notes' => 'Activating free tier',
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $order = Order::first();

        $this->assertNotNull($order);
        $this->assertSame(PaymentStatus::Paid, $order->payment_status);
        $this->assertSame(PaymentMode::Free, $order->payment_mode);
        $this->assertSame(0.0, (float) $order->final_amount);
        $this->assertSame('Activating free tier', $order->payment_notes);
        $this->assertSame(2, $order->jobs_allowed);
        $this->assertNotNull($order->paid_at);
        $this->assertIsArray($order->payment_meta);
        $this->assertArrayHasKey('recorded_ip', $order->payment_meta);

        $subscription = EmployerSubscription::first();

        $this->assertNotNull($subscription);
        $this->assertSame(SubscriptionStatus::Active, $subscription->status);
        $this->assertSame(2, $subscription->jobs_allowed);
        $this->assertSame(0, $subscription->jobs_used);
        $this->assertSame(14, $subscription->job_duration_days);
        $this->assertTrue($subscription->ends_at->isAfter(now()->addDays(25)));
    }

    public function test_employer_paid_plan_goes_through_gateway_then_activates(): void
    {
        $plan = Plan::factory()->create([
            'title' => 'Pro',
            'plan_type' => PlanType::Premium,
            'amount' => 1999,
            'discount_amount' => 1499,
            'jobs_allowed' => 10,
        ]);

        $response = $this->actingAs($this->employer, 'employer')
            ->post("/employer/plans/{$plan->id}/purchase", [
                'payment_mode' => 'upi',
            ]);

        $order = Order::first();
        $this->assertNotNull($order);
        $this->assertSame(PaymentStatus::Pending, $order->payment_status);
        $this->assertSame(PaymentMode::Upi, $order->payment_mode);
        $this->assertSame(0, EmployerSubscription::count());

        $response->assertRedirect(route('employer.payments.fake.complete', $order));

        $this->actingAs($this->employer, 'employer')
            ->get(route('employer.payments.fake.complete', $order))
            ->assertRedirect(route('employer.orders.show', $order))
            ->assertSessionHas('success');

        $order->refresh();

        $this->assertSame(PaymentStatus::Paid, $order->payment_status);
        $this->assertNotNull($order->transaction_reference);
        $this->assertNotNull($order->paid_at);
        $this->assertSame(1499.0, (float) $order->final_amount);

        $this->assertDatabaseHas('employer_subscriptions', [
            'employer_id' => $this->employer->id,
            'plan_id' => $plan->id,
            'status' => 'active',
            'jobs_allowed' => 10,
        ]);
    }

    public function test_paid_plan_requires_a_gateway_payment_mode(): void
    {
        $plan = Plan::factory()->create(['amount' => 500, 'plan_type' => PlanType::Basic]);

        $this->actingAs($this->employer, 'employer')
            ->post("/employer/plans/{$plan->id}/purchase", [])
            ->assertSessionHasErrors('payment_mode');

        $this->actingAs($this->employer, 'employer')
            ->post("/employer/plans/{$plan->id}/purchase", [
                'payment_mode' => 'cash',
            ])
            ->assertSessionHasErrors('payment_mode');
    }

    public function test_buying_a_new_plan_cancels_the_previous_active_subscription(): void
    {
        $first = Plan::factory()->free()->create(['jobs_allowed' => 1]);
        $second = Plan::factory()->create([
            'amount' => 100,
            'jobs_allowed' => 3,
            'plan_type' => PlanType::Basic,
        ]);

        $this->actingAs($this->employer, 'employer')
            ->post("/employer/plans/{$first->id}/purchase")
            ->assertRedirect();

        $start = $this->actingAs($this->employer, 'employer')
            ->post("/employer/plans/{$second->id}/purchase", [
                'payment_mode' => 'razorpay',
            ]);

        $order = Order::query()->where('plan_id', $second->id)->firstOrFail();
        $start->assertRedirect(route('employer.payments.fake.complete', $order));

        $this->actingAs($this->employer, 'employer')
            ->get(route('employer.payments.fake.complete', $order))
            ->assertRedirect();

        $this->assertSame(1, EmployerSubscription::query()->where('status', 'active')->count());
        $this->assertSame(1, EmployerSubscription::query()->where('status', 'cancelled')->count());
        $this->assertSame(4, EmployerSubscription::query()->where('status', 'active')->value('jobs_allowed'));
    }

    public function test_buying_another_plan_carries_unused_job_credits(): void
    {
        $free = Plan::factory()->free()->create(['jobs_allowed' => 1]);
        $starter = Plan::factory()->create([
            'amount' => 399,
            'jobs_allowed' => 1,
            'plan_type' => PlanType::Basic,
        ]);
        $quarterly = Plan::factory()->create([
            'amount' => 1299,
            'jobs_allowed' => 1,
            'plan_type' => PlanType::Standard,
        ]);

        $this->actingAs($this->employer, 'employer')
            ->post("/employer/plans/{$free->id}/purchase")
            ->assertRedirect();

        $this->buyPaidPlan($starter);
        $this->buyPaidPlan($quarterly);

        $active = EmployerSubscription::query()->where('status', 'active')->first();

        $this->assertNotNull($active);
        $this->assertSame(3, $active->jobs_allowed);
        $this->assertSame(0, $active->jobs_used);
        $this->assertSame(2, EmployerSubscription::query()->where('status', 'cancelled')->count());
    }

    protected function buyPaidPlan(Plan $plan): void
    {
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

    public function test_orders_index_shows_purchase_history(): void
    {
        $plan = Plan::factory()->free()->create();

        $this->actingAs($this->employer, 'employer')
            ->post("/employer/plans/{$plan->id}/purchase")
            ->assertRedirect();

        $order = Order::first();

        $this->actingAs($this->employer, 'employer')
            ->get('/employer/orders')
            ->assertOk()
            ->assertSee($order->order_number)
            ->assertSee('Free');
    }
}
