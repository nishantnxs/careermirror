<?php

namespace App\Enums;

enum DurationUnit: string
{
    case Day = 'day';
    case Week = 'week';
    case Month = 'month';
    case Year = 'year';
    case Lifetime = 'lifetime';

    public function label(): string
    {
        return match ($this) {
            self::Day => 'Day(s)',
            self::Week => 'Week(s)',
            self::Month => 'Month(s)',
            self::Year => 'Year(s)',
            self::Lifetime => 'Lifetime',
        };
    }

    /** Number of days this unit represents, used to compute a plan end date. */
    public function days(): ?int
    {
        return match ($this) {
            self::Day => 1,
            self::Week => 7,
            self::Month => 30,
            self::Year => 365,
            self::Lifetime => null,
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
