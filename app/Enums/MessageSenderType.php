<?php

namespace App\Enums;

enum MessageSenderType: string
{
    case Candidate = 'candidate';
    case Employer = 'employer';

    public function label(): string
    {
        return match ($this) {
            self::Candidate => 'Candidate',
            self::Employer => 'Employer',
        };
    }
}
