<?php

namespace App\Enums;

enum JobStatus: string
{
    case Draft = 'draft';
    case Published = 'published';
    case Paused = 'paused';
    case Closed = 'closed';
    case Expired = 'expired';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Published => 'Published',
            self::Paused => 'Paused',
            self::Closed => 'Closed',
            self::Expired => 'Expired',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Draft => 'bg-secondary-subtle text-secondary-emphasis',
            self::Published => 'bg-success-subtle text-success-emphasis',
            self::Paused => 'bg-warning-subtle text-warning-emphasis',
            self::Closed => 'bg-dark-subtle text-dark-emphasis',
            self::Expired => 'bg-danger-subtle text-danger-emphasis',
        };
    }
}
