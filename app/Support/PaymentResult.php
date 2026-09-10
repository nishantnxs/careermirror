<?php

namespace App\Support;

class PaymentResult
{
    /**
     * @param  array<string, mixed>  $meta
     */
    public function __construct(
        public readonly bool $successful,
        public readonly ?string $transactionReference = null,
        public readonly array $meta = [],
        public readonly ?string $message = null,
    ) {}

    /**
     * @param  array<string, mixed>  $meta
     */
    public static function success(string $transactionReference, array $meta = []): self
    {
        return new self(true, $transactionReference, $meta);
    }

    /**
     * @param  array<string, mixed>  $meta
     */
    public static function failed(string $message, array $meta = []): self
    {
        return new self(false, null, $meta, $message);
    }
}
