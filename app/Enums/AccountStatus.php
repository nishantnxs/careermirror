<?php

namespace App\Enums;

enum AccountStatus: string
{
    case Active = 'active';
    case Inactive = 'inactive';
    case Suspended = 'suspended';

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Active',
            self::Inactive => 'Inactive',
            self::Suspended => 'Suspended',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Active => 'bg-success-subtle text-success-emphasis',
            self::Inactive => 'bg-secondary-subtle text-secondary-emphasis',
            self::Suspended => 'bg-danger-subtle text-danger-emphasis',
        };
    }

    public function canAuthenticate(): bool
    {
        return $this === self::Active;
    }

    public function authenticationErrorMessage(): string
    {
        return match ($this) {
            self::Suspended => 'This account has been suspended.',
            self::Inactive => 'This account has been deactivated.',
            self::Active => 'These credentials do not match our records.',
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
