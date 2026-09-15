<?php

namespace App\Enums;

enum ApplicationStatus: string
{
    case Applied = 'applied';
    case UnderReview = 'under_review';
    case Shortlisted = 'shortlisted';
    case InterviewScheduled = 'interview_scheduled';
    case Selected = 'selected';
    case Rejected = 'rejected';
    case Withdrawn = 'withdrawn';

    public function label(): string
    {
        return match ($this) {
            self::Applied => 'Applied',
            self::UnderReview => 'Under Review',
            self::Shortlisted => 'Shortlisted',
            self::InterviewScheduled => 'Interview Scheduled',
            self::Selected => 'Selected',
            self::Rejected => 'Rejected',
            self::Withdrawn => 'Withdrawn',
        };
    }

    public function badgeLabel(): string
    {
        return match ($this) {
            self::Applied => 'submitted',
            default => strtolower($this->label()),
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Applied => 'bg-primary-subtle text-primary-emphasis',
            self::UnderReview => 'bg-info-subtle text-info-emphasis',
            self::Shortlisted => 'bg-success-subtle text-success-emphasis',
            self::InterviewScheduled => 'bg-warning-subtle text-warning-emphasis',
            self::Selected => 'bg-success text-white',
            self::Rejected => 'bg-danger-subtle text-danger-emphasis',
            self::Withdrawn => 'bg-secondary-subtle text-secondary-emphasis',
        };
    }

    public function isTerminal(): bool
    {
        return in_array($this, [self::Selected, self::Rejected, self::Withdrawn], true);
    }

    /** @return array<string, string> */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $case) => [$case->value => $case->label()])
            ->all();
    }
}
