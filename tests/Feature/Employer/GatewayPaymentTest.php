<?php

namespace Tests\Feature\Employer;

use App\Enums\PaymentMode;
use App\Enums\PaymentStatus;
use App\Models\Employer;
use App\Models\Order;
use App\Models\Plan;
use App\Payments\Gateways\PaypalGateway;
use App\Payments\Gateways\RazorpayGateway;
use App\Payments\Gateways\StripeGateway;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class GatewayPaymentTest extends TestCase
{
    use RefreshDatabase;

    protected Employer $employer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->employer = Employer::factory()->create([
            'email' => 'employer@example.com',
            'phone' => '9876543210',
        ]);
    }

    public function test_razorpay_checkout_creates_gateway_order_and_verifies_signature(): void
    {
        config([
            'payments.driver' => 'live',
            'payments.razorpay.key' => 'rzp_test_key',
            'payments.razorpay.secret' => 'rzp_test_secret',
        ]);

        Http::fake([
            'api.razorpay.com/*' => Http::response(['id' => 'order_RZP123'], 200),
        ]);

        $plan = Plan::factory()->create(['amount' => 100, 'currency' => 'INR']);
        $order = Order::factory()->create([
            'employer_id' => $this->employer->id,
            'plan_id' => $plan->id,
            'plan_title' => $plan->title,
            'currency' => 'INR',
            'amount' => 100,
            'final_amount' => 100,
            'payment_status' => PaymentStatus::Pending,
            'payment_mode' => PaymentMode::Razorpay,
            'payment_meta' => [],
        ]);

        $checkout = app(RazorpayGateway::class)->createCheckout($order->fresh('employer'), PaymentMode::Razorpay);

        $this->assertSame('embed', $checkout->type);
        $this->assertSame('order_RZP123', $checkout->payload['order_id']);
        $this->assertSame('rzp_test_key', $checkout->payload['key']);

        $paymentId = 'pay_ABC';
        $signature = hash_hmac('sha256', 'order_RZP123|'.$paymentId, 'rzp_test_secret');

        $result = app(RazorpayGateway::class)->verify($order->fresh(), [
            'razorpay_payment_id' => $paymentId,
            'razorpay_order_id' => 'order_RZP123',
            'razorpay_signature' => $signature,
        ]);

        $this->assertTrue($result->successful);
        $this->assertSame($paymentId, $result->transactionReference);
    }

    public function test_stripe_checkout_session_and_verification(): void
    {
        config([
            'payments.driver' => 'live',
            'payments.stripe.secret' => 'sk_test_secret',
        ]);

        Http::fake([
            'api.stripe.com/v1/checkout/sessions' => Http::response([
                'id' => 'cs_test_123',
                'url' => 'https://checkout.stripe.com/c/pay/cs_test_123',
            ], 200),
            'api.stripe.com/v1/checkout/sessions/*' => Http::response([
                'id' => 'cs_test_123',
                'payment_status' => 'paid',
                'payment_intent' => 'pi_123',
            ], 200),
        ]);

        $plan = Plan::factory()->create(['amount' => 50, 'currency' => 'USD']);
        $order = Order::factory()->create([
            'employer_id' => $this->employer->id,
            'plan_id' => $plan->id,
            'plan_title' => $plan->title,
            'currency' => 'USD',
            'amount' => 50,
            'final_amount' => 50,
            'payment_status' => PaymentStatus::Pending,
            'payment_mode' => PaymentMode::Stripe,
            'payment_meta' => [],
        ]);

        $checkout = app(StripeGateway::class)->createCheckout($order->fresh('employer'), PaymentMode::Stripe);

        $this->assertSame('redirect', $checkout->type);
        $this->assertSame('https://checkout.stripe.com/c/pay/cs_test_123', $checkout->redirectUrl);

        $result = app(StripeGateway::class)->verify($order->fresh(), [
            'session_id' => 'cs_test_123',
        ]);

        $this->assertTrue($result->successful);
        $this->assertSame('pi_123', $result->transactionReference);
    }

    public function test_paypal_checkout_converts_inr_to_usd(): void
    {
        config([
            'payments.driver' => 'live',
            'payments.paypal.client_id' => 'paypal_client',
            'payments.paypal.client_secret' => 'paypal_secret',
            'payments.paypal.base_url' => 'https://api-m.sandbox.paypal.com',
            'payments.paypal.charge_currency' => 'USD',
            'payments.paypal.inr_to_usd_rate' => 0.01,
        ]);

        Http::fake([
            'api-m.sandbox.paypal.com/v1/oauth2/token' => Http::response([
                'access_token' => 'paypal_token',
            ], 200),
            'api-m.sandbox.paypal.com/v2/checkout/orders' => Http::response([
                'id' => 'PAYPAL_ORDER_INR',
                'links' => [
                    ['rel' => 'approve', 'href' => 'https://www.sandbox.paypal.com/checkoutnow?token=PAYPAL_ORDER_INR'],
                ],
            ], 200),
        ]);

        $plan = Plan::factory()->create(['amount' => 1000, 'currency' => 'INR']);
        $order = Order::factory()->create([
            'employer_id' => $this->employer->id,
            'plan_id' => $plan->id,
            'plan_title' => $plan->title,
            'currency' => 'INR',
            'amount' => 1000,
            'final_amount' => 1000,
            'payment_status' => PaymentStatus::Pending,
            'payment_mode' => PaymentMode::Paypal,
            'payment_meta' => [],
        ]);

        app(PaypalGateway::class)->createCheckout($order->fresh('employer'), PaymentMode::Paypal);

        Http::assertSent(function ($request) {
            if (! str_contains($request->url(), '/v2/checkout/orders') || $request->method() !== 'POST') {
                return false;
            }

            $amount = $request['purchase_units'][0]['amount'] ?? null;

            return ($amount['currency_code'] ?? null) === 'USD'
                && ($amount['value'] ?? null) === '10.00';
        });

        $this->assertSame('USD', $order->fresh()->payment_meta['paypal_charge_currency']);
        $this->assertSame('10.00', $order->fresh()->payment_meta['paypal_charge_amount']);
    }

    public function test_paypal_checkout_and_capture(): void
    {
        config([
            'payments.driver' => 'live',
            'payments.paypal.client_id' => 'paypal_client',
            'payments.paypal.client_secret' => 'paypal_secret',
            'payments.paypal.base_url' => 'https://api-m.sandbox.paypal.com',
        ]);

        Http::fake([
            'api-m.sandbox.paypal.com/v1/oauth2/token' => Http::response([
                'access_token' => 'paypal_token',
            ], 200),
            'api-m.sandbox.paypal.com/v2/checkout/orders' => Http::response([
                'id' => 'PAYPAL_ORDER_1',
                'links' => [
                    ['rel' => 'approve', 'href' => 'https://www.sandbox.paypal.com/checkoutnow?token=PAYPAL_ORDER_1'],
                ],
            ], 200),
            'api-m.sandbox.paypal.com/v2/checkout/orders/PAYPAL_ORDER_1/capture' => Http::response([
                'id' => 'PAYPAL_ORDER_1',
                'status' => 'COMPLETED',
                'purchase_units' => [
                    ['payments' => ['captures' => [['id' => 'CAPTURE_1']]]],
                ],
            ], 200),
        ]);

        $plan = Plan::factory()->create(['amount' => 75, 'currency' => 'USD']);
        $order = Order::factory()->create([
            'employer_id' => $this->employer->id,
            'plan_id' => $plan->id,
            'plan_title' => $plan->title,
            'currency' => 'USD',
            'amount' => 75,
            'final_amount' => 75,
            'payment_status' => PaymentStatus::Pending,
            'payment_mode' => PaymentMode::Paypal,
            'payment_meta' => [],
        ]);

        $checkout = app(PaypalGateway::class)->createCheckout($order->fresh('employer'), PaymentMode::Paypal);

        $this->assertSame('redirect', $checkout->type);
        $this->assertStringContainsString('PAYPAL_ORDER_1', $checkout->redirectUrl);

        $result = app(PaypalGateway::class)->verify($order->fresh(), [
            'token' => 'PAYPAL_ORDER_1',
        ]);

        $this->assertTrue($result->successful);
        $this->assertSame('CAPTURE_1', $result->transactionReference);
    }
}
