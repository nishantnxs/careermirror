<?php

namespace App\Payments;

use App\Enums\PaymentMode;
use App\Payments\Contracts\PaymentGateway;
use App\Payments\Gateways\FakeGateway;
use App\Payments\Gateways\PaypalGateway;
use App\Payments\Gateways\RazorpayGateway;
use App\Payments\Gateways\StripeGateway;
use InvalidArgumentException;

class PaymentGatewayManager
{
    public function driver(?PaymentMode $mode = null): PaymentGateway
    {
        if (config('payments.driver') === 'fake') {
            return app(FakeGateway::class);
        }

        return match ($mode) {
            PaymentMode::Paypal => app(PaypalGateway::class),
            PaymentMode::Stripe => app(StripeGateway::class),
            PaymentMode::Razorpay, PaymentMode::Upi => app(RazorpayGateway::class),
            default => throw new InvalidArgumentException('Unsupported payment gateway.'),
        };
    }
}
