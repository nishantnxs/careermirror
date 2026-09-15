<?php

namespace App\Http\Controllers;

use App\Models\JobPosting;
use App\Services\CandidateJobSearchService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class JobBoardController extends Controller
{
    public function __construct(public CandidateJobSearchService $search) {}

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'keyword' => ['nullable', 'string', 'max:150'],
            'location' => ['nullable', 'string', 'max:150'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'job_type' => ['nullable', 'string', 'max:50'],
            'experience_level' => ['nullable', 'string', 'max:50'],
            'salary_min' => ['nullable', 'numeric', 'min:0'],
            'salary_max' => ['nullable', 'numeric', 'min:0'],
            'posted_within' => ['nullable', 'in:1,7,14,30'],
            'job' => ['nullable', 'integer'],
        ]);

        $selectedJobId = isset($filters['job']) ? (int) $filters['job'] : null;
        unset($filters['job']);

        $jobs = $this->search->search($filters, perPage: 20);

        if ($selectedJobId && ! $jobs->getCollection()->contains('id', $selectedJobId)) {
            $selectedJobId = $jobs->first()?->id;
        }

        if ($selectedJobId === null) {
            $selectedJobId = $jobs->first()?->id;
        }

        $candidate = $request->user('candidate');
        $pageJobIds = $jobs->getCollection()->modelKeys();

        return view('jobs.index', [
            'jobs' => $jobs,
            'filters' => $filters,
            'categories' => $this->search->categories(),
            'jobTypes' => $this->search->jobTypeOptions(),
            'selectedJobId' => $selectedJobId,
            'siteName' => setting('site_name', config('app.name')),
            'resumes' => $candidate?->resumes()->latest('id')->get() ?? collect(),
            'defaultResumeId' => $candidate?->defaultResume()?->id,
            'appliedJobIds' => $candidate
                ? $candidate->applications()
                    ->whereIn('job_posting_id', $pageJobIds)
                    ->pluck('job_posting_id')
                    ->map(fn ($id) => (int) $id)
                    ->all()
                : [],
            'savedJobIds' => $candidate
                ? $candidate->savedJobs()
                    ->whereIn('job_posting_id', $pageJobIds)
                    ->pluck('job_posting_id')
                    ->map(fn ($id) => (int) $id)
                    ->all()
                : [],
        ]);
    }

    public function show(JobPosting $job): RedirectResponse
    {
        $visible = JobPosting::query()
            ->available()
            ->forDomain(current_domain())
            ->whereKey($job->id)
            ->exists();

        abort_unless($visible, 404);

        return redirect()->route('jobs.index', ['job' => $job->id]);
    }
}
