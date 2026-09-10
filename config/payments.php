<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Payment driver
    |--------------------------------------------------------------------------
    |
    | Use "fake" locally/tests without real keys. Set "live" in production
    | after configuring Razorpay, Stripe, and PayPal credentials.
    |
    */

    'driver' => env('PAYMENT_DRIVER', 'fake'),

    'razorpay' => [
        'mode' => env('RAZORPAY_MODE', 'test'), // test|live
        'key' => env('RAZORPAY_KEY'),
        'secret' => env('RAZORPAY_SECRET'),
        'base_url' => env('RAZORPAY_BASE_URL', 'https://api.razorpay.com/v1'),
    ],

    'stripe' => [
        'key' => env('STRIPE_KEY'),
        'secret' => env('STRIPE_SECRET'),
        'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
    ],

    'paypal' => [
        'client_id' => env('PAYPAL_CLIENT_ID'),
        'client_secret' => env('PAYPAL_CLIENT_SECRET'),
        'mode' => env('PAYPAL_MODE', 'sandbox'),
        'base_url' => env(
            'PAYPAL_BASE_URL',
            env('PAYPAL_MODE', 'sandbox') === 'live'
                ? 'https://api-m.paypal.com'
                : 'https://api-m.sandbox.paypal.com'
        ),
        // Sandbox/merchant accounts often do not accept INR — charge PayPal in this currency instead.
        'charge_currency' => env('PAYPAL_CURRENCY', 'USD'),
        // Used when converting INR plan prices to the PayPal charge currency.
        'inr_to_usd_rate' => (float) env('PAYPAL_INR_TO_USD_RATE', 0.012),
    ],

];
