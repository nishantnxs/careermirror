<?php

namespace App\Models;

use App\Enums\JobStatus;
use Database\Factories\JobPostingFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JobPosting extends Model
{
    /** @use HasFactory<JobPostingFactory> */
    use HasFactory;

    protected $fillable = [
        'employer_id',
        'employer_subscription_id',
        'category_id',
        'title',
        'description',
        'location',
        'job_type',
        'experience_level',
        'salary_min',
        'salary_max',
        'currency',
        'status',
        'published_at',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => JobStatus::class,
            'salary_min' => 'decimal:2',
            'salary_max' => 'decimal:2',
            'published_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    public function employer(): BelongsTo
    {
        return $this->belongsTo(Employer::class);
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(EmployerSubscription::class, 'employer_subscription_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function domains(): BelongsToMany
    {
        return $this->belongsToMany(Domain::class, 'domain_job_posting')->withTimestamps();
    }

    public function applications(): HasMany
    {
        return $this->hasMany(JobApplication::class);
    }

    public function savedByCandidates(): HasMany
    {
        return $this->hasMany(SavedJob::class);
    }

    public function scopeForDomain(Builder $query, Domain|int|null $domain): Builder
    {
        if ($domain === null) {
            return $query;
        }

        $domainId = $domain instanceof Domain ? $domain->id : $domain;

        return $query->whereHas('domains', fn (Builder $query) => $query->where('domains.id', $domainId));
    }

    public function scopeAvailable(Builder $query): Builder
    {
        return $query
            ->where('status', JobStatus::Published)
            ->where(function (Builder $query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>=', now());
            });
    }

    public function isAvailable(): bool
    {
        if ($this->status !== JobStatus::Published) {
            return false;
        }

        return $this->expires_at === null || $this->expires_at->isFuture() || $this->expires_at->isCurrentSecond();
    }

    public function salaryLabel(): string
    {
        if ($this->salary_min === null && $this->salary_max === null) {
            return 'Not disclosed';
        }

        $currency = $this->currency ?: 'INR';

        if ($this->salary_min !== null && $this->salary_max !== null) {
            return sprintf('%s %s – %s', $currency, number_format((float) $this->salary_min), number_format((float) $this->salary_max));
        }

        if ($this->salary_min !== null) {
            return sprintf('%s %s+', $currency, number_format((float) $this->salary_min));
        }

        return sprintf('Up to %s %s', $currency, number_format((float) $this->salary_max));
    }
}
