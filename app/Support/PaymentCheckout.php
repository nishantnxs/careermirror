<?php

namespace App\Support;

class PaymentCheckout
{
    /**
     * @param  array<string, mixed>  $payload
     */
    public function __construct(
        public readonly string $type,
        public readonly ?string $redirectUrl = null,
        public readonly array $payload = [],
    ) {}

    public static function redirect(string $url, array $payload = []): self
    {
        return new self('redirect', $url, $payload);
    }

    public static function embed(array $payload): self
    {
        return new self('embed', null, $payload);
    }
}
