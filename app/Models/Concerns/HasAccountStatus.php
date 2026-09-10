<?php

namespace App\Models\Concerns;

use App\Enums\AccountStatus;
use Illuminate\Database\Eloquent\Builder;

trait HasAccountStatus
{
    public function canAuthenticate(): bool
    {
        return $this->status->canAuthenticate();
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', AccountStatus::Active);
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        return $query->when($term, function (Builder $query) use ($term) {
            $query->where(function (Builder $query) use ($term) {
                $query->where('name', 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%")
                    ->orWhere('phone', 'like', "%{$term}%");
            });
        });
    }

    public function scopeStatus(Builder $query, ?string $status): Builder
    {
        return $query->when(
            $status && AccountStatus::tryFrom($status),
            fn (Builder $query) => $query->where('status', $status),
        );
    }
}
