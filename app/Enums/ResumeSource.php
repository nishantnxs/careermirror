<?php

namespace App\Enums;

enum ResumeSource: string
{
    case Builder = 'builder';
    case Upload = 'upload';

    public function label(): string
    {
        return match ($this) {
            self::Builder => 'Built in CareerMirror',
            self::Upload => 'Uploaded file',
        };
    }
}
