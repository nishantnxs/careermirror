<?php

namespace App\Services;

use App\Enums\ApplicationStatus;
use App\Models\Candidate;
use App\Models\CandidateResume;
use App\Models\JobApplication;
use App\Models\JobPosting;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class JobApplicationService
{
    public function apply(
        Candidate $candidate,
        JobPosting $job,
        CandidateResume $resume,
        ?string $coverLetter = null,
    ): JobApplication {
        if (! $job->isAvailable()) {
            throw ValidationException::withMessages([
                'job' => 'This job is no longer accepting applications.',
            ]);
        }

        if ($resume->candidate_id !== $candidate->id) {
            throw ValidationException::withMessages([
                'candidate_resume_id' => 'Select one of your own resumes.',
            ]);
        }

        return DB::transaction(function () use ($candidate, $job, $resume, $coverLetter) {
            $existing = JobApplication::query()
                ->where('candidate_id', $candidate->id)
                ->where('job_posting_id', $job->id)
                ->lockForUpdate()
                ->first();

            if ($existing && $existing->status !== ApplicationStatus::Withdrawn) {
                throw ValidationException::withMessages([
                    'job' => 'You have already applied for this job.',
                ]);
            }

            if ($existing) {
                $existing->update([
                    'candidate_resume_id' => $resume->id,
                    'cover_letter' => $coverLetter,
                    'status' => ApplicationStatus::Applied,
                    'applied_at' => now(),
                    'withdrawn_at' => null,
                ]);

                return $existing->fresh(['jobPosting.employer', 'resume']);
            }

            return JobApplication::create([
                'candidate_id' => $candidate->id,
                'job_posting_id' => $job->id,
                'candidate_resume_id' => $resume->id,
                'cover_letter' => $coverLetter,
                'status' => ApplicationStatus::Applied,
                'applied_at' => now(),
            ])->load(['jobPosting.employer', 'resume']);
        });
    }

    public function withdraw(JobApplication $application): JobApplication
    {
        if (! $application->canBeWithdrawn()) {
            throw ValidationException::withMessages([
                'application' => 'This application can no longer be withdrawn.',
            ]);
        }

        $application->update([
            'status' => ApplicationStatus::Withdrawn,
            'withdrawn_at' => now(),
        ]);

        return $application->fresh();
    }

    public function updateStatus(JobApplication $application, ApplicationStatus $status): JobApplication
    {
        if ($application->status === ApplicationStatus::Withdrawn) {
            throw ValidationException::withMessages([
                'status' => 'A withdrawn application cannot be updated.',
            ]);
        }

        if ($status === ApplicationStatus::Withdrawn) {
            throw ValidationException::withMessages([
                'status' => 'Only the candidate can withdraw an application.',
            ]);
        }

        $application->update(['status' => $status]);

        return $application->fresh();
    }
}
