<?php

namespace App\Support;

use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use InvalidArgumentException;

class DateRange
{
    public function __construct(
        public readonly CarbonImmutable $start,
        public readonly CarbonImmutable $end,
        public readonly string $preset,
    ) {}

    public static function fromRequest(Request $request): self
    {
        $preset = $request->string('range')->toString() ?: 'this_month';

        return match ($preset) {
            'today' => self::today(),
            'this_week' => self::thisWeek(),
            'this_month' => self::thisMonth(),
            'last_month' => self::lastMonth(),
            'this_year' => self::thisYear(),
            'custom' => self::custom(
                $request->string('start_date')->toString(),
                $request->string('end_date')->toString(),
            ),
            default => self::thisMonth(),
        };
    }

    public static function today(): self
    {
        $today = CarbonImmutable::now()->startOfDay();

        return new self($today, $today->endOfDay(), 'today');
    }

    public static function thisWeek(): self
    {
        $now = CarbonImmutable::now();

        return new self($now->startOfWeek(), $now->endOfWeek()->endOfDay(), 'this_week');
    }

    public static function thisMonth(): self
    {
        $now = CarbonImmutable::now();

        return new self($now->startOfMonth(), $now->endOfMonth()->endOfDay(), 'this_month');
    }

    public static function lastMonth(): self
    {
        $lastMonth = CarbonImmutable::now()->subMonthNoOverflow();

        return new self($lastMonth->startOfMonth(), $lastMonth->endOfMonth()->endOfDay(), 'last_month');
    }

    public static function thisYear(): self
    {
        $now = CarbonImmutable::now();

        return new self($now->startOfYear(), $now->endOfYear()->endOfDay(), 'this_year');
    }

    public static function custom(string $startDate, string $endDate): self
    {
        if ($startDate === '' || $endDate === '') {
            throw new InvalidArgumentException('Custom date ranges require both start and end dates.');
        }

        $start = CarbonImmutable::parse($startDate)->startOfDay();
        $end = CarbonImmutable::parse($endDate)->endOfDay();

        if ($end->lt($start)) {
            [$start, $end] = [$end->startOfDay(), $start->endOfDay()];
        }

        return new self($start, $end, 'custom');
    }

    /** @return array<string, string> */
    public static function presetOptions(): array
    {
        return [
            'today' => 'Today',
            'this_week' => 'This week',
            'this_month' => 'This month',
            'last_month' => 'Last month',
            'this_year' => 'This year',
            'custom' => 'Custom date range',
        ];
    }

    public function label(): string
    {
        if ($this->preset === 'custom') {
            return $this->start->format('d M Y').' – '.$this->end->format('d M Y');
        }

        return self::presetOptions()[$this->preset] ?? 'Selected period';
    }
}
