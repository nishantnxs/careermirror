<?php

namespace App\Models;

use App\Enums\DurationUnit;
use App\Enums\PlanType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
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
            'duration_value' => 'integer',
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
        $base = Str::slug($title) ?: 'plan';
        $slug = $base;
        $suffix = 1;

        while (static::withTrashed()
            ->where('slug', $slug)
            ->when($ignoreId, fn (Builder $query) => $query->whereKeyNot($ignoreId))
            ->exists()
        ) {
            $slug = $base.'-'.++$suffix;
        }

        return $slug;
    }

    /** The price a customer actually pays. */
    public function getPayableAmountAttribute(): float
    {
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

    /** Total days of access this plan grants, or null for lifetime. */
    public function getDurationInDaysAttribute(): ?int
    {
        $days = $this->duration_unit->days();

        return $days === null ? null : $days * $this->duration_value;
    }

    public function getIsExpiredAttribute(): bool
    {
        return $this->expiry_date !== null && $this->expiry_date->isPast();
    }

    public function getFormattedAmountAttribute(): string
    {
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
