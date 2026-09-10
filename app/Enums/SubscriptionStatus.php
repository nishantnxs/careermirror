<?php

namespace App\Enums;

enum SubscriptionStatus: string
{
    case Active = 'active';
    case Expired = 'expired';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Active',
            self::Expired => 'Expired',
            self::Cancelled => 'Cancelled',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Active => 'bg-success-subtle text-success-emphasis',
            self::Expired => 'bg-secondary-subtle text-secondary-emphasis',
            self::Cancelled => 'bg-danger-subtle text-danger-emphasis',
        };
    }
}
