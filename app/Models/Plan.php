<?php

namespace App\Models;

use App\Enums\DurationUnit;
use App\Enums\PlanType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Plan extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'slug',
        'plan_type',
        'description',
        'duration_value',
        'duration_unit',
        'currency',
        'amount',
        'discount_amount',
        'trial_days',
        'jobs_allowed',
        'job_duration_value',
        'job_duration_unit',
        'features',
        'expiry_date',
        'is_featured',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'plan_type' => PlanType::class,
            'duration_unit' => DurationUnit::class,
            'job_duration_unit' => DurationUnit::class,
            'duration_value' => 'integer',
            'job_duration_value' => 'integer',
            'jobs_allowed' => 'integer',
            'trial_days' => 'integer',
            'sort_order' => 'integer',
            'amount' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'features' => 'array',
            'expiry_date' => 'date',
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (self $plan): void {
            if (blank($plan->slug)) {
                $plan->slug = static::uniqueSlug($plan->title, $plan->id);
            }
        });
    }

    public static function uniqueSlug(string $title, ?int $ignoreId = null): string
    {
        return unique_slug(
            $title,
            fn (string $slug): bool => static::withTrashed()
                ->where('slug', $slug)
                ->when($ignoreId, fn (Builder $query) => $query->whereKeyNot($ignoreId))
                ->exists(),
            'plan',
        );
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function domains(): BelongsToMany
    {
        return $this->belongsToMany(Domain::class, 'domain_plan')->withTimestamps();
    }

    public function isFree(): bool
    {
        return $this->plan_type === PlanType::Free || (float) $this->payable_amount <= 0;
    }

    /** The price a customer actually pays. */
    public function getPayableAmountAttribute(): float
    {
        if ($this->plan_type === PlanType::Free) {
            return 0.0;
        }

        $discount = $this->discount_amount;

        if ($discount !== null && (float) $discount < (float) $this->amount) {
            return (float) $discount;
        }

        return (float) $this->amount;
    }

    public function getDiscountPercentAttribute(): ?int
    {
        $amount = (float) $this->amount;

        if ($amount <= 0 || $this->discount_amount === null) {
            return null;
        }

        $percent = (int) round((($amount - $this->payable_amount) / $amount) * 100);

        return $percent > 0 ? $percent : null;
    }

    public function getDurationLabelAttribute(): string
    {
        if ($this->duration_unit === DurationUnit::Lifetime) {
            return 'Lifetime';
        }

        return $this->duration_value.' '.Str::plural(
            ucfirst($this->duration_unit->value),
            $this->duration_value
        );
    }

    public function getJobDurationLabelAttribute(): string
    {
        if ($this->job_duration_unit === DurationUnit::Lifetime) {
            return 'Lifetime';
        }

        return $this->job_duration_value.' '.Str::plural(
            ucfirst($this->job_duration_unit->value),
            $this->job_duration_value
        );
    }

    /** Total days of access this plan grants, or null for lifetime. */
    public function getDurationInDaysAttribute(): ?int
    {
        $days = $this->duration_unit->days();

        return $days === null ? null : $days * $this->duration_value;
    }

    public function getJobDurationInDaysAttribute(): ?int
    {
        $days = $this->job_duration_unit->days();

        return $days === null ? null : $days * $this->job_duration_value;
    }

    public function getIsExpiredAttribute(): bool
    {
        return $this->expiry_date !== null && $this->expiry_date->isPast();
    }

    public function getFormattedAmountAttribute(): string
    {
        if ($this->isFree()) {
            return 'Free';
        }

        return $this->currencySymbol().number_format($this->payable_amount, 2);
    }

    public function currencySymbol(): string
    {
        return match ($this->currency) {
            'INR' => '₹',
            'USD' => '$',
            'EUR' => '€',
            'GBP' => '£',
            default => $this->currency.' ',
        };
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeAvailable(Builder $query): Builder
    {
        return $query->active()->where(function (Builder $query) {
            $query->whereNull('expiry_date')->orWhereDate('expiry_date', '>=', now()->toDateString());
        });
    }

    public function scopeForDomain(Builder $query, Domain|int|null $domain): Builder
    {
        if ($domain === null) {
            return $query;
        }

        $domainId = $domain instanceof Domain ? $domain->id : $domain;

        return $query->whereHas('domains', fn (Builder $query) => $query->where('domains.id', $domainId));
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        return $query->when($term, function (Builder $query) use ($term) {
            $query->where(function (Builder $query) use ($term) {
                $query->where('title', 'like', "%{$term}%")
                    ->orWhere('description', 'like', "%{$term}%");
            });
        });
    }
}
