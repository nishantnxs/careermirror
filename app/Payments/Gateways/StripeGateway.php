<?php

namespace App\Payments\Gateways;

use App\Enums\PaymentMode;
use App\Models\Order;
use App\Payments\Contracts\PaymentGateway;
use App\Support\PaymentCheckout;
use App\Support\PaymentResult;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class StripeGateway implements PaymentGateway
{
    public function createCheckout(Order $order, PaymentMode $mode): PaymentCheckout
    {
        $secret = config('payments.stripe.secret');

        if (blank($secret)) {
            throw new RuntimeException('Stripe is not configured. Set STRIPE_SECRET.');
        }

        if (str_starts_with((string) $secret, 'pk_')) {
            throw new RuntimeException('STRIPE_SECRET must be the secret key (sk_test_... / sk_live_...), not the publishable key (pk_...).');
        }

        $amountMinor = (int) round(((float) $order->final_amount) * 100);

        $response = Http::withToken($secret)
            ->asForm()
            ->post('https://api.stripe.com/v1/checkout/sessions', [
                'mode' => 'payment',
                'success_url' => route('employer.payments.stripe.success', $order).'?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => route('employer.payments.stripe.cancel', $order),
                'client_reference_id' => $order->order_number,
                'customer_email' => $order->employer?->email,
                'line_items[0][quantity]' => 1,
                'line_items[0][price_data][currency]' => strtolower($order->currency),
                'line_items[0][price_data][unit_amount]' => $amountMinor,
                'line_items[0][price_data][product_data][name]' => $order->plan_title,
                'metadata[order_number]' => $order->order_number,
                'metadata[employer_id]' => (string) $order->employer_id,
                'metadata[plan_id]' => (string) $order->plan_id,
            ]);

        if (! $response->successful()) {
            throw new RuntimeException('Unable to create Stripe checkout session: '.$response->body());
        }

        $sessionId = $response->json('id');
        $url = $response->json('url');

        $order->update([
            'payment_meta' => array_merge($order->payment_meta ?? [], [
                'gateway' => 'stripe',
                'stripe_session_id' => $sessionId,
            ]),
        ]);

        return PaymentCheckout::redirect($url, [
            'gateway' => 'stripe',
            'session_id' => $sessionId,
        ]);
    }

    public function verify(Order $order, array $payload): PaymentResult
    {
        $secret = config('payments.stripe.secret');

        if (blank($secret)) {
            throw new RuntimeException('Stripe is not configured.');
        }

        $sessionId = (string) ($payload['session_id'] ?? $order->payment_meta['stripe_session_id'] ?? '');

        if ($sessionId === '') {
            return PaymentResult::failed('Missing Stripe session id.');
        }

        $response = Http::withToken($secret)
            ->get('https://api.stripe.com/v1/checkout/sessions/'.$sessionId);

        if (! $response->successful()) {
            return PaymentResult::failed('Unable to verify Stripe session.');
        }

        $paymentStatus = $response->json('payment_status');
        $reference = $response->json('payment_intent') ?: $sessionId;

        if ($paymentStatus !== 'paid') {
            return PaymentResult::failed('Stripe payment is not completed yet.', [
                'stripe_session' => $response->json(),
            ]);
        }

        return PaymentResult::success((string) $reference, [
            'gateway' => 'stripe',
            'stripe_session_id' => $sessionId,
            'stripe_payment_intent' => $response->json('payment_intent'),
            'stripe_customer_email' => $response->json('customer_details.email'),
        ]);
    }
}
