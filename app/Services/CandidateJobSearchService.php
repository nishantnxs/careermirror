<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Domain;
use App\Models\JobPosting;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class CandidateJobSearchService
{
    /**
     * @param  array<string, mixed>  $filters
     * @return LengthAwarePaginator<int, JobPosting>
     */
    public function search(array $filters, Domain|int|null $domain = null, int $perPage = 12, bool $forCurrentDomain = true): LengthAwarePaginator
    {
        $query = JobPosting::query()
            ->available()
            ->with(['employer', 'category'])
            ->latest('published_at')
            ->latest('id');

        if ($forCurrentDomain) {
            $query->forDomain($domain ?? current_domain());
        }

        $this->applyFilters($query, $filters);

        return $query->paginate($perPage)->withQueryString();
    }

    /**
     * @param  Builder<JobPosting>  $query
     * @param  array<string, mixed>  $filters
     */
    public function applyFilters(Builder $query, array $filters): void
    {
        $keyword = trim((string) ($filters['keyword'] ?? ''));
        if ($keyword !== '') {
            $query->where(function (Builder $query) use ($keyword) {
                $query->where('title', 'like', "%{$keyword}%")
                    ->orWhere('description', 'like', "%{$keyword}%")
                    ->orWhere('location', 'like', "%{$keyword}%")
                    ->orWhereHas('employer', fn (Builder $query) => $query->where('company_name', 'like', "%{$keyword}%"));
            });
        }

        $location = trim((string) ($filters['location'] ?? ''));
        if ($location !== '') {
            $query->where('location', 'like', "%{$location}%");
        }

        if (! empty($filters['category_id'])) {
            $query->where('category_id', (int) $filters['category_id']);
        }

        if (! empty($filters['job_type'])) {
            $query->where('job_type', $filters['job_type']);
        }

        if (! empty($filters['experience_level'])) {
            $query->where('experience_level', $filters['experience_level']);
        }

        if (isset($filters['salary_min']) && $filters['salary_min'] !== '' && $filters['salary_min'] !== null) {
            $query->where(function (Builder $query) use ($filters) {
                $query->whereNull('salary_max')
                    ->orWhere('salary_max', '>=', (float) $filters['salary_min']);
            });
        }

        if (isset($filters['salary_max']) && $filters['salary_max'] !== '' && $filters['salary_max'] !== null) {
            $query->where(function (Builder $query) use ($filters) {
                $query->whereNull('salary_min')
                    ->orWhere('salary_min', '<=', (float) $filters['salary_max']);
            });
        }

        $posted = $filters['posted_within'] ?? null;
        if (is_string($posted) && $posted !== '') {
            $days = match ($posted) {
                '1' => 1,
                '7' => 7,
                '14' => 14,
                '30' => 30,
                default => null,
            };

            if ($days !== null) {
                $query->where('published_at', '>=', now()->subDays($days));
            }
        }
    }

    /** @return array<int, string> */
    public function jobTypeOptions(bool $forCurrentDomain = true): array
    {
        $query = JobPosting::query()
            ->available()
            ->whereNotNull('job_type')
            ->where('job_type', '!=', '');

        if ($forCurrentDomain) {
            $query->forDomain(current_domain());
        }

        return $query
            ->distinct()
            ->orderBy('job_type')
            ->pluck('job_type')
            ->mapWithKeys(fn (string $type) => [$type => ucwords(str_replace(['-', '_'], ' ', $type))])
            ->all();
    }

    /** @return array<int, string> */
    public function experienceOptions(bool $forCurrentDomain = true): array
    {
        $query = JobPosting::query()
            ->available()
            ->whereNotNull('experience_level')
            ->where('experience_level', '!=', '');

        if ($forCurrentDomain) {
            $query->forDomain(current_domain());
        }

        return $query
            ->distinct()
            ->orderBy('experience_level')
            ->pluck('experience_level')
            ->mapWithKeys(fn (string $level) => [$level => ucwords(str_replace(['-', '_'], ' ', $level))])
            ->all();
    }

    /** @return Collection<int, Category> */
    public function categories()
    {
        return Category::query()->approved()->ordered()->get();
    }
}
