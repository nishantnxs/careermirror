<?php

namespace App\Models;

use App\Enums\SubscriptionStatus;
use Database\Factories\EmployerSubscriptionFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EmployerSubscription extends Model
{
    /** @use HasFactory<EmployerSubscriptionFactory> */
    use HasFactory;

    protected $fillable = [
        'employer_id',
        'order_id',
        'plan_id',
        'status',
        'starts_at',
        'ends_at',
        'jobs_allowed',
        'jobs_used',
        'job_duration_days',
    ];

    protected function casts(): array
    {
        return [
            'status' => SubscriptionStatus::class,
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'jobs_allowed' => 'integer',
            'jobs_used' => 'integer',
            'job_duration_days' => 'integer',
        ];
    }

    public function employer(): BelongsTo
    {
        return $this->belongsTo(Employer::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function jobPostings(): HasMany
    {
        return $this->hasMany(JobPosting::class);
    }

    public function isCurrentlyActive(): bool
    {
        if ($this->status !== SubscriptionStatus::Active) {
            return false;
        }

        if ($this->ends_at !== null && $this->ends_at->isPast()) {
            return false;
        }

        return true;
    }

    public function remainingJobs(): int
    {
        return max(0, $this->jobs_allowed - $this->jobs_used);
    }

    public function canPostJob(): bool
    {
        return $this->isCurrentlyActive() && $this->remainingJobs() > 0;
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', SubscriptionStatus::Active)
            ->where(function (Builder $query) {
                $query->whereNull('ends_at')->orWhere('ends_at', '>', now());
            });
    }

    public function scopeWithRemainingCredits(Builder $query): Builder
    {
        return $query->whereColumn('jobs_used', '<', 'jobs_allowed');
    }
}
