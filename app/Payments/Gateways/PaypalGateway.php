<?php

namespace App\Payments\Gateways;

use App\Enums\PaymentMode;
use App\Models\Order;
use App\Payments\Contracts\PaymentGateway;
use App\Support\PaymentCheckout;
use App\Support\PaymentResult;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class PaypalGateway implements PaymentGateway
{
    /**
     * Currencies commonly accepted by PayPal sandbox / standard merchant accounts.
     *
     * @var list<string>
     */
    protected array $supportedCurrencies = [
        'AUD', 'BRL', 'CAD', 'CZK', 'DKK', 'EUR', 'HKD', 'HUF', 'ILS', 'JPY',
        'MYR', 'MXN', 'TWD', 'NZD', 'NOK', 'PHP', 'PLN', 'GBP', 'SGD', 'SEK',
        'CHF', 'THB', 'USD',
    ];

    public function createCheckout(Order $order, PaymentMode $mode): PaymentCheckout
    {
        $accessToken = $this->accessToken();
        $charge = $this->chargeAmount($order);

        $response = Http::withToken($accessToken)
            ->acceptJson()
            ->post(rtrim((string) config('payments.paypal.base_url'), '/').'/v2/checkout/orders', [
                'intent' => 'CAPTURE',
                'purchase_units' => [[
                    'reference_id' => substr($order->order_number, 0, 127),
                    'description' => substr($order->plan_title, 0, 127),
                    'amount' => [
                        'currency_code' => $charge['currency'],
                        'value' => $charge['amount'],
                    ],
                    'custom_id' => (string) $order->id,
                ]],
                'application_context' => [
                    'brand_name' => setting('site_name', config('app.name')),
                    'landing_page' => 'NO_PREFERENCE',
                    'user_action' => 'PAY_NOW',
                    'return_url' => route('employer.payments.paypal.return', $order),
                    'cancel_url' => route('employer.payments.paypal.cancel', $order),
                ],
            ]);

        if (! $response->successful()) {
            throw new RuntimeException($this->friendlyCreateError($response->json(), $response->body()));
        }

        $paypalOrderId = $response->json('id');
        $approveUrl = collect($response->json('links', []))
            ->firstWhere('rel', 'approve')['href'] ?? null;

        if (blank($approveUrl)) {
            throw new RuntimeException('PayPal did not return an approval URL.');
        }

        $order->update([
            'payment_meta' => array_merge($order->payment_meta ?? [], [
                'gateway' => 'paypal',
                'paypal_order_id' => $paypalOrderId,
                'paypal_charge_currency' => $charge['currency'],
                'paypal_charge_amount' => $charge['amount'],
                'paypal_original_currency' => $charge['original_currency'],
                'paypal_original_amount' => $charge['original_amount'],
                'paypal_conversion_rate' => $charge['rate'],
            ]),
        ]);

        return PaymentCheckout::redirect($approveUrl, [
            'gateway' => 'paypal',
            'paypal_order_id' => $paypalOrderId,
        ]);
    }

    public function verify(Order $order, array $payload): PaymentResult
    {
        $paypalOrderId = (string) ($payload['token']
            ?? $payload['paypal_order_id']
            ?? $order->payment_meta['paypal_order_id']
            ?? '');

        if ($paypalOrderId === '') {
            return PaymentResult::failed('Missing PayPal order token.');
        }

        // Prefer the order id stored when checkout started.
        $storedOrderId = (string) ($order->payment_meta['paypal_order_id'] ?? '');
        if ($storedOrderId !== '' && $storedOrderId !== $paypalOrderId) {
            $paypalOrderId = $storedOrderId;
        }

        $response = Http::withToken($this->accessToken())
            ->withHeaders([
                'Prefer' => 'return=representation',
                'PayPal-Request-Id' => (string) str()->uuid(),
            ])
            ->acceptJson()
            ->withBody('{}', 'application/json')
            ->post(rtrim((string) config('payments.paypal.base_url'), '/').'/v2/checkout/orders/'.$paypalOrderId.'/capture');

        if (! $response->successful()) {
            return PaymentResult::failed($this->friendlyCaptureError($response->json(), $response->body()));
        }

        $status = $response->json('status');
        $captureId = data_get($response->json(), 'purchase_units.0.payments.captures.0.id', $paypalOrderId);

        if ($status !== 'COMPLETED') {
            return PaymentResult::failed('PayPal payment was not completed.', [
                'paypal_capture' => $response->json(),
            ]);
        }

        return PaymentResult::success((string) $captureId, [
            'gateway' => 'paypal',
            'paypal_order_id' => $paypalOrderId,
            'paypal_capture_id' => $captureId,
            'paypal_payer_email' => data_get($response->json(), 'payer.email_address'),
            'paypal_captured_amount' => data_get($response->json(), 'purchase_units.0.payments.captures.0.amount'),
        ]);
    }

    /**
     * @return array{
     *     currency: string,
     *     amount: string,
     *     original_currency: string,
     *     original_amount: string,
     *     rate: float|null
     * }
     */
    protected function chargeAmount(Order $order): array
    {
        $originalCurrency = strtoupper($order->currency);
        $originalAmount = (float) $order->final_amount;
        $preferred = strtoupper((string) config('payments.paypal.charge_currency', 'USD'));

        if (in_array($originalCurrency, $this->supportedCurrencies, true)) {
            return [
                'currency' => $originalCurrency,
                'amount' => number_format($originalAmount, 2, '.', ''),
                'original_currency' => $originalCurrency,
                'original_amount' => number_format($originalAmount, 2, '.', ''),
                'rate' => null,
            ];
        }

        $rate = match ($originalCurrency) {
            'INR' => (float) config('payments.paypal.inr_to_usd_rate', 0.012),
            default => null,
        };

        if ($rate === null || $preferred !== 'USD') {
            throw new RuntimeException(sprintf(
                'PayPal does not support %s for this merchant account. Use Razorpay/UPI for INR plans, or set PAYPAL_CURRENCY=USD.',
                $originalCurrency,
            ));
        }

        $converted = max(0.01, round($originalAmount * $rate, 2));

        return [
            'currency' => $preferred,
            'amount' => number_format($converted, 2, '.', ''),
            'original_currency' => $originalCurrency,
            'original_amount' => number_format($originalAmount, 2, '.', ''),
            'rate' => $rate,
        ];
    }

    /**
     * @param  array<string, mixed>|null  $json
     */
    protected function friendlyCreateError(?array $json, string $body): string
    {
        $issue = data_get($json, 'details.0.issue');

        if ($issue === 'CURRENCY_NOT_SUPPORTED') {
            return 'PayPal does not support this plan currency for your account. INR plans are charged in USD automatically — please retry, or pay with Razorpay/UPI.';
        }

        return 'Unable to create PayPal order: '.($json['message'] ?? $body);
    }

    /**
     * @param  array<string, mixed>|null  $json
     */
    protected function friendlyCaptureError(?array $json, string $body): string
    {
        $issue = data_get($json, 'details.0.issue');
        $detail = data_get($json, 'details.0.description');

        if ($issue === 'ORDER_NOT_APPROVED') {
            return 'PayPal payment was not approved. Please complete payment on PayPal and try again.';
        }

        if ($issue === 'ORDER_ALREADY_CAPTURED') {
            return 'This PayPal payment was already captured. Check your Orders page.';
        }

        $message = $json['message'] ?? $body;

        if (is_string($detail) && $detail !== '') {
            $message .= ' '.$detail;
        }

        return 'Unable to capture PayPal payment: '.$message;
    }

    protected function accessToken(): string
    {
        $clientId = config('payments.paypal.client_id');
        $clientSecret = config('payments.paypal.client_secret');

        if (blank($clientId) || blank($clientSecret)) {
            throw new RuntimeException('PayPal is not configured. Set PAYPAL_CLIENT_ID and PAYPAL_CLIENT_SECRET.');
        }

        return Cache::remember('paypal_access_token', now()->addMinutes(50), function () use ($clientId, $clientSecret) {
            $response = Http::asForm()
                ->withBasicAuth($clientId, $clientSecret)
                ->post(rtrim((string) config('payments.paypal.base_url'), '/').'/v1/oauth2/token', [
                    'grant_type' => 'client_credentials',
                ]);

            if (! $response->successful() || blank($response->json('access_token'))) {
                throw new RuntimeException('Unable to authenticate with PayPal.');
            }

            return (string) $response->json('access_token');
        });
    }
}
