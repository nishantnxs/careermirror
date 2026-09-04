<?php

namespace App\Enums;

enum PlanType: string
{
    case Free = 'free';
    case Basic = 'basic';
    case Standard = 'standard';
    case Premium = 'premium';
    case Enterprise = 'enterprise';

    public function label(): string
    {
        return match ($this) {
            self::Free => 'Free',
            self::Basic => 'Basic',
            self::Standard => 'Standard',
            self::Premium => 'Premium',
            self::Enterprise => 'Enterprise',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Free => 'bg-secondary',
            self::Basic => 'bg-info',
            self::Standard => 'bg-primary',
            self::Premium => 'bg-warning text-dark',
            self::Enterprise => 'bg-dark',
        };
    }

    /** @return array<string, string> */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $case) => [$case->value => $case->label()])
            ->all();
    }
}
