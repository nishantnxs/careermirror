<?php

namespace App\Services;

use App\Enums\AccountStatus;
use App\Models\Candidate;
use App\Models\Domain;
use App\Models\Employer;
use App\Models\JobApplication;
use App\Models\JobPosting;
use App\Models\Plan;
use App\Support\DateRange;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class AdminDashboardService
{
    /**
     * @return array{
     *     range: DateRange,
     *     stats: array<string, int|float>,
     *     recent_candidates: Collection<int, Candidate>,
     *     recent_employers: Collection<int, Employer>,
     * }
     */
    public function overview(DateRange $range): array
    {
        $domainId = app(DomainService::class)->adminContextDomainId();

        $employers = Employer::query()
            ->when($domainId, fn (Builder $query) => $query->whereHas(
                'domains',
                fn (Builder $query) => $query->where('domains.id', $domainId)
            ));

        $jobs = JobPosting::query()->forDomain($domainId);
        $plans = Plan::query()->forDomain($domainId);

        return [
            'range' => $range,
            'stats' => [
                'total_candidates' => $this->countInRange(Candidate::query(), $range),
                'total_employers' => $this->countInRange(clone $employers, $range),
                'active_candidates' => Candidate::query()->active()->count(),
                'active_employers' => (clone $employers)->active()->count(),
                'jobs_posted' => (clone $jobs)->whereBetween('created_at', [$range->start, $range->end])->count(),
                'active_jobs' => (clone $jobs)->where('status', 'published')->count(),
                'expired_jobs' => (clone $jobs)->whereNotNull('expires_at')->where('expires_at', '<', now())->count(),
                'featured_jobs' => 0,
                'plans_available' => (clone $plans)->count(),
                'plans_purchased' => 0,
                'revenue' => 0.0,
                'applications' => JobApplication::query()
                    ->whereBetween('applied_at', [$range->start, $range->end])
                    ->when($domainId, function (Builder $query) use ($domainId) {
                        $query->whereHas(
                            'jobPosting',
                            fn (Builder $query) => $query->forDomain($domainId)
                        );
                    })
                    ->count(),
                'total_domains' => Domain::query()->count(),
                'active_domains' => Domain::query()->active()->count(),
            ],
            'recent_candidates' => Candidate::query()->latest()->take(5)->get(),
            'recent_employers' => (clone $employers)->latest()->take(5)->get(),
            'account_status_counts' => [
                'candidates' => $this->statusCounts(Candidate::query()),
                'employers' => $this->statusCounts(clone $employers),
            ],
            'domain_filter' => $domainId,
        ];
    }

    /** @param  Builder<Model>  $query */
    protected function countInRange(Builder $query, DateRange $range): int
    {
        return (clone $query)
            ->whereBetween('created_at', [$range->start, $range->end])
            ->count();
    }

    /**
     * @param  Builder<Model>  $query
     * @return array<string, int>
     */
    protected function statusCounts(Builder $query): array
    {
        $counts = (clone $query)
            ->selectRaw('status, COUNT(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status')
            ->all();

        return collect(AccountStatus::cases())
            ->mapWithKeys(fn (AccountStatus $status) => [
                $status->value => (int) ($counts[$status->value] ?? 0),
            ])
            ->all();
    }
}
