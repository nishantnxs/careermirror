<?php

namespace App\Models;

use App\Enums\ApplicationStatus;
use Database\Factories\JobApplicationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobApplication extends Model
{
    /** @use HasFactory<JobApplicationFactory> */
    use HasFactory;

    protected $fillable = [
        'candidate_id',
        'job_posting_id',
        'candidate_resume_id',
        'cover_letter',
        'status',
        'applied_at',
        'withdrawn_at',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'status' => ApplicationStatus::class,
            'applied_at' => 'datetime',
            'withdrawn_at' => 'datetime',
        ];
    }

    public function candidate(): BelongsTo
    {
        return $this->belongsTo(Candidate::class);
    }

    public function jobPosting(): BelongsTo
    {
        return $this->belongsTo(JobPosting::class);
    }

    public function resume(): BelongsTo
    {
        return $this->belongsTo(CandidateResume::class, 'candidate_resume_id');
    }

    public function canBeWithdrawn(): bool
    {
        return ! in_array($this->status, [
            ApplicationStatus::Withdrawn,
            ApplicationStatus::Selected,
            ApplicationStatus::Rejected,
        ], true);
    }
}
