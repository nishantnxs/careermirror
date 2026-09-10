<?php

namespace App\Payments\Gateways;

use App\Enums\PaymentMode;
use App\Models\Order;
use App\Payments\Contracts\PaymentGateway;
use App\Support\PaymentCheckout;
use App\Support\PaymentResult;
use RuntimeException;

class FakeGateway implements PaymentGateway
{
    public function createCheckout(Order $order, PaymentMode $mode): PaymentCheckout
    {
        return PaymentCheckout::redirect(route('employer.payments.fake.complete', $order), [
            'gateway' => 'fake',
            'payment_mode' => $mode->value,
        ]);
    }

    public function verify(Order $order, array $payload): PaymentResult
    {
        if ($order->payment_status->value !== 'pending') {
            throw new RuntimeException('Order is not awaiting payment.');
        }

        return PaymentResult::success(
            'FAKE-'.$order->order_number,
            [
                'gateway' => 'fake',
                'payment_mode' => $order->payment_mode?->value,
                'verified_at' => now()->toIso8601String(),
            ],
        );
    }
}
