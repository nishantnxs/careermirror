<?php

namespace App\Services;

use App\Enums\ActivityAction;
use App\Enums\JobStatus;
use App\Models\Employer;
use App\Models\EmployerSubscription;
use App\Models\JobPosting;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use RuntimeException;

class JobPostingService
{
    public function __construct(
        public ActivityLogger $activity,
        public CategoryService $categories,
    ) {}

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(Employer $employer, array $attributes): JobPosting
    {
        return DB::transaction(function () use ($employer, $attributes) {
            /** @var EmployerSubscription|null $subscription */
            $subscription = EmployerSubscription::query()
                ->where('employer_id', $employer->id)
                ->active()
                ->lockForUpdate()
                ->latest('id')
                ->first();

            if ($subscription === null || ! $subscription->canPostJob()) {
                throw new RuntimeException('An active plan with remaining job credits is required to post a job.');
            }

            $category = $this->categories->resolveForJob(
                $employer,
                $attributes['category_id'] ?? null,
                $attributes['custom_category_name'] ?? null,
            );

            $status = JobStatus::from($attributes['status'] ?? JobStatus::Published->value);
            $publishedAt = $status === JobStatus::Published ? now() : null;
            $expiresAt = null;

            if ($status === JobStatus::Published && $subscription->job_duration_days !== null) {
                $expiresAt = now()->addDays($subscription->job_duration_days);
            }

            $job = JobPosting::create([
                'employer_id' => $employer->id,
                'employer_subscription_id' => $subscription->id,
                'category_id' => $category->id,
                'title' => $attributes['title'],
                'description' => $attributes['description'],
                'location' => $attributes['location'] ?? null,
                'job_type' => $attributes['job_type'] ?? null,
                'experience_level' => $attributes['experience_level'] ?? null,
                'salary_min' => $attributes['salary_min'] ?? null,
                'salary_max' => $attributes['salary_max'] ?? null,
                'currency' => $attributes['currency'] ?? 'INR',
                'status' => $status,
                'published_at' => $publishedAt,
                'expires_at' => $expiresAt,
            ]);

            $job->domains()->sync($attributes['domain_ids'] ?? []);

            $subscription->increment('jobs_used');

            $this->activity->record(
                ActivityAction::JobCreated,
                $job,
                null,
                $job->only([
                    'title', 'status', 'location', 'job_type', 'experience_level',
                    'salary_min', 'salary_max', 'currency', 'expires_at', 'category_id',
                ]),
                "Job \"{$job->title}\" created.",
            );

            return $job;
        });
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function update(JobPosting $job, array $attributes): JobPosting
    {
        if ($job->employer_id !== auth('employer')->id()) {
            throw new InvalidArgumentException('You can only update your own job postings.');
        }

        $employer = $job->employer;
        $category = $this->categories->resolveForJob(
            $employer,
            $attributes['category_id'] ?? null,
            $attributes['custom_category_name'] ?? null,
        );

        $before = $job->only([
            'title', 'description', 'location', 'job_type', 'experience_level',
            'salary_min', 'salary_max', 'currency', 'status', 'published_at', 'expires_at', 'category_id',
        ]);

        $status = isset($attributes['status'])
            ? JobStatus::from($attributes['status'])
            : $job->status;

        $publishedAt = $job->published_at;
        $expiresAt = $job->expires_at;
        $previousExpiresAt = $job->expires_at;

        if ($status === JobStatus::Published && $job->status !== JobStatus::Published) {
            $publishedAt = now();
            $days = $job->subscription?->job_duration_days;
            $expiresAt = $days === null ? null : now()->addDays($days);
        }

        $job->update([
            'category_id' => $category->id,
            'title' => $attributes['title'],
            'description' => $attributes['description'],
            'location' => $attributes['location'] ?? null,
            'job_type' => $attributes['job_type'] ?? null,
            'experience_level' => $attributes['experience_level'] ?? null,
            'salary_min' => $attributes['salary_min'] ?? null,
            'salary_max' => $attributes['salary_max'] ?? null,
            'currency' => $attributes['currency'] ?? $job->currency,
            'status' => $status,
            'published_at' => $publishedAt,
            'expires_at' => $expiresAt,
        ]);

        $job->domains()->sync($attributes['domain_ids'] ?? []);

        $job = $job->fresh();
        $after = $job->only([
            'title', 'description', 'location', 'job_type', 'experience_level',
            'salary_min', 'salary_max', 'currency', 'status', 'published_at', 'expires_at', 'category_id',
        ]);

        [$old, $new] = $this->activity->diff($before, $after);

        if ($old !== [] || $new !== []) {
            $this->activity->record(
                ActivityAction::JobUpdated,
                $job,
                $old,
                $new,
                "Job \"{$job->title}\" updated.",
            );
        }

        if (
            $previousExpiresAt !== null
            && $job->expires_at !== null
            && $job->expires_at->gt($previousExpiresAt)
        ) {
            $this->activity->record(
                ActivityAction::JobExpiryExtended,
                $job,
                ['expires_at' => $previousExpiresAt],
                ['expires_at' => $job->expires_at],
                "Job \"{$job->title}\" expiry extended.",
            );
        }

        return $job;
    }
}
