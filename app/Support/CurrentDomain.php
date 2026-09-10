<?php

namespace App\Support;

use App\Models\Domain;

class CurrentDomain
{
    protected ?Domain $domain = null;

    protected bool $resolved = false;

    public function get(): ?Domain
    {
        return $this->domain;
    }

    public function id(): ?int
    {
        return $this->domain?->id;
    }

    public function set(?Domain $domain): void
    {
        $this->domain = $domain;
        $this->resolved = true;
    }

    public function clear(): void
    {
        $this->domain = null;
        $this->resolved = false;
    }

    public function resolved(): bool
    {
        return $this->resolved;
    }

    public function require(): Domain
    {
        if ($this->domain === null) {
            throw new \RuntimeException('No current domain is available for this request.');
        }

        return $this->domain;
    }
}
