<?php

namespace App\Enums;

enum CategoryStatus: string
{
    case Approved = 'approved';
    case Pending = 'pending';
    case Rejected = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::Approved => 'Approved',
            self::Pending => 'Pending approval',
            self::Rejected => 'Rejected',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Approved => 'bg-success-subtle text-success-emphasis',
            self::Pending => 'bg-warning-subtle text-warning-emphasis',
            self::Rejected => 'bg-danger-subtle text-danger-emphasis',
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
