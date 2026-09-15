<?php

namespace App\Support;

final readonly class GoogleUserProfile
{
    public function __construct(
        public string $id,
        public string $name,
        public string $email,
    ) {}
}
