<?php

namespace App\Payments\Gateways;

use App\Enums\PaymentMode;
use App\Models\Order;
use App\Payments\Contracts\PaymentGateway;
use App\Support\PaymentCheckout;
use App\Support\PaymentResult;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class RazorpayGateway implements PaymentGateway
{
    public function createCheckout(Order $order, PaymentMode $mode): PaymentCheckout
    {
        $key = config('payments.razorpay.key');
        $secret = config('payments.razorpay.secret');

        if (blank($key) || blank($secret)) {
            throw new RuntimeException('Razorpay is not configured. Set RAZORPAY_KEY and RAZORPAY_SECRET.');
        }

        $modeSetting = config('payments.razorpay.mode', 'test');

        if ($modeSetting === 'test' && ! str_starts_with((string) $key, 'rzp_test_')) {
            throw new RuntimeException('Razorpay is set to test mode, but RAZORPAY_KEY is not a test key (rzp_test_*).');
        }

        if ($modeSetting === 'live' && ! str_starts_with((string) $key, 'rzp_live_')) {
            throw new RuntimeException('Razorpay is set to live mode, but RAZORPAY_KEY is not a live key (rzp_live_*).');
        }

        $amountMinor = (int) round(((float) $order->final_amount) * 100);

        $response = Http::withBasicAuth($key, $secret)
            ->acceptJson()
            ->post(rtrim((string) config('payments.razorpay.base_url'), '/').'/orders', [
                'amount' => $amountMinor,
                'currency' => strtoupper($order->currency),
                'receipt' => $order->order_number,
                'notes' => [
                    'order_number' => $order->order_number,
                    'employer_id' => (string) $order->employer_id,
                    'plan_id' => (string) $order->plan_id,
                    'payment_mode' => $mode->value,
                ],
            ]);

        if (! $response->successful()) {
            throw new RuntimeException('Unable to create Razorpay order: '.$response->body());
        }

        $razorpayOrderId = $response->json('id');

        $order->update([
            'payment_meta' => array_merge($order->payment_meta ?? [], [
                'razorpay_order_id' => $razorpayOrderId,
                'gateway' => 'razorpay',
                'preferred_method' => $mode === PaymentMode::Upi ? 'upi' : null,
            ]),
        ]);

        $employer = $order->employer;

        return PaymentCheckout::embed([
            'gateway' => 'razorpay',
            'key' => $key,
            'amount' => $amountMinor,
            'currency' => strtoupper($order->currency),
            'name' => setting('site_name', config('app.name')),
            'description' => $order->plan_title,
            'order_id' => $razorpayOrderId,
            'prefill' => [
                'name' => $employer?->name,
                'email' => $employer?->email,
                'contact' => $employer?->phone,
            ],
            'notes' => [
                'order_number' => $order->order_number,
            ],
            'theme' => [
                'color' => '#0d6efd',
            ],
            'method' => $mode === PaymentMode::Upi ? ['upi' => true] : null,
            'confirm_url' => route('employer.payments.razorpay.confirm', $order),
        ]);
    }

    public function verify(Order $order, array $payload): PaymentResult
    {
        $secret = config('payments.razorpay.secret');

        if (blank($secret)) {
            throw new RuntimeException('Razorpay is not configured.');
        }

        $paymentId = (string) ($payload['razorpay_payment_id'] ?? '');
        $razorpayOrderId = (string) ($payload['razorpay_order_id'] ?? '');
        $signature = (string) ($payload['razorpay_signature'] ?? '');

        if ($paymentId === '' || $razorpayOrderId === '' || $signature === '') {
            return PaymentResult::failed('Missing Razorpay payment confirmation fields.');
        }

        $expected = hash_hmac('sha256', $razorpayOrderId.'|'.$paymentId, $secret);

        if (! hash_equals($expected, $signature)) {
            return PaymentResult::failed('Invalid Razorpay payment signature.');
        }

        $storedOrderId = $order->payment_meta['razorpay_order_id'] ?? null;

        if ($storedOrderId && $storedOrderId !== $razorpayOrderId) {
            return PaymentResult::failed('Razorpay order mismatch.');
        }

        return PaymentResult::success($paymentId, [
            'gateway' => 'razorpay',
            'razorpay_order_id' => $razorpayOrderId,
            'razorpay_payment_id' => $paymentId,
            'razorpay_signature' => $signature,
        ]);
    }
}
