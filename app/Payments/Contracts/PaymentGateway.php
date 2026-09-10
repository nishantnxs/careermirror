<?php

namespace App\Payments\Contracts;

use App\Enums\PaymentMode;
use App\Models\Order;
use App\Support\PaymentCheckout;
use App\Support\PaymentResult;

interface PaymentGateway
{
    /**
     * Start a gateway checkout for a pending order.
     */
    public function createCheckout(Order $order, PaymentMode $mode): PaymentCheckout;

    /**
     * Verify and capture a completed gateway payment.
     *
     * @param  array<string, mixed>  $payload
     */
    public function verify(Order $order, array $payload): PaymentResult;
}
