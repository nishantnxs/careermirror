<?php

namespace App\Enums;

enum PaymentMode: string
{
    case Free = 'free';
    case Paypal = 'paypal';
    case Stripe = 'stripe';
    case Razorpay = 'razorpay';
    case Upi = 'upi';
    case BankTransfer = 'bank_transfer';
    case Card = 'card';
    case Cash = 'cash';
    case Cheque = 'cheque';
    case AdminAssigned = 'admin_assigned';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Free => 'Free',
            self::Paypal => 'PayPal',
            self::Stripe => 'Stripe',
            self::Razorpay => 'Razorpay',
            self::Upi => 'UPI',
            self::BankTransfer => 'Bank transfer',
            self::Card => 'Card',
            self::Cash => 'Cash',
            self::Cheque => 'Cheque',
            self::AdminAssigned => 'Assigned by admin',
            self::Other => 'Other',
        };
    }

    public function isGateway(): bool
    {
        return in_array($this, [self::Paypal, self::Stripe, self::Razorpay, self::Upi], true);
    }

    public function requiresReference(): bool
    {
        return match ($this) {
            self::Free, self::Cash, self::AdminAssigned,
            self::Paypal, self::Stripe, self::Razorpay, self::Upi => false,
            default => true,
        };
    }

    /** @return array<string, string> */
    public static function gatewayOptions(): array
    {
        return [
            self::Razorpay->value => self::Razorpay->label(),
            self::Upi->value => self::Upi->label(),
            self::Stripe->value => self::Stripe->label(),
            self::Paypal->value => self::Paypal->label(),
        ];
    }

    /** @return array<string, string> */
    public static function purchaseOptions(): array
    {
        return self::gatewayOptions();
    }

    /** @return array<string, string> */
    public static function adminAssignmentOptions(): array
    {
        return collect(self::cases())
            ->reject(fn (self $case) => $case === self::Free)
            ->mapWithKeys(fn (self $case) => [$case->value => $case->label()])
            ->all();
    }
}
