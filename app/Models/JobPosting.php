<?php

namespace App\Models;

use App\Enums\JobStatus;
use Database\Factories\JobPostingFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

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

    public function scopeForDomain($query, Domain|int|null $domain)
    {
        if ($domain === null) {
            return $query;
        }

        $domainId = $domain instanceof Domain ? $domain->id : $domain;

        return $query->whereHas('domains', fn ($query) => $query->where('domains.id', $domainId));
    }
}
