<?php

namespace App\Models;

use App\Enums\CategoryStatus;
use Database\Factories\CategoryFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    /** @use HasFactory<CategoryFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'status',
        'suggestion_count',
        'suggested_by_employer_id',
        'reviewed_by_admin_id',
        'reviewed_at',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'status' => CategoryStatus::class,
            'suggestion_count' => 'integer',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
            'reviewed_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (self $category): void {
            if (blank($category->slug)) {
                $category->slug = static::uniqueSlug($category->name, $category->id);
            }
        });
    }

    public static function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        return unique_slug(
            $name,
            fn (string $slug): bool => static::query()
                ->where('slug', $slug)
                ->when($ignoreId, fn (Builder $query) => $query->whereKeyNot($ignoreId))
                ->exists(),
            'category',
        );
    }

    public function suggestedBy(): BelongsTo
    {
        return $this->belongsTo(Employer::class, 'suggested_by_employer_id');
    }

    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'reviewed_by_admin_id');
    }

    public function jobPostings(): HasMany
    {
        return $this->hasMany(JobPosting::class);
    }

    /**
     * Limit to categories that currently have a public job on the given domain.
     */
    public function scopeWithAvailableJobs(Builder $query, Domain|int|null $domain = null): Builder
    {
        return $query->whereHas('jobPostings', function (Builder $query) use ($domain) {
            $query->available()->forDomain($domain ?? current_domain());
        });
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        return $query->when($term, function (Builder $query) use ($term) {
            $query->where(function (Builder $query) use ($term) {
                $query->where('name', 'like', "%{$term}%")
                    ->orWhere('slug', 'like', "%{$term}%");
            });
        });
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', CategoryStatus::Approved)
            ->where('is_active', true);
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', CategoryStatus::Pending);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    public function scopeMatchingName(Builder $query, string $name): Builder
    {
        return $query->whereRaw('LOWER(name) = ?', [mb_strtolower(trim($name))]);
    }

    public function isApproved(): bool
    {
        return $this->status === CategoryStatus::Approved && $this->is_active;
    }

    public function isPending(): bool
    {
        return $this->status === CategoryStatus::Pending;
    }
}
