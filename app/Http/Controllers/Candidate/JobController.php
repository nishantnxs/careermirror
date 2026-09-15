<?php

namespace App\Http\Controllers\Candidate;

use App\Http\Controllers\Controller;
use App\Http\Requests\Candidate\ApplyJobRequest;
use App\Http\Requests\Candidate\JobSearchRequest;
use App\Models\JobPosting;
use App\Services\CandidateJobSearchService;
use App\Services\JobApplicationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class JobController extends Controller
{
    public function __construct(
        public CandidateJobSearchService $search,
        public JobApplicationService $applications,
    ) {}

    public function index(JobSearchRequest $request): View
    {
        $filters = $request->filters();
        $candidate = $request->user('candidate');

        $jobs = $this->search->search($filters, forCurrentDomain: false);

        $appliedJobIds = $candidate->applications()
            ->whereIn('job_posting_id', $jobs->getCollection()->modelKeys())
            ->pluck('job_posting_id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $savedJobIds = $candidate->savedJobs()
            ->whereIn('job_posting_id', $jobs->getCollection()->modelKeys())
            ->pluck('job_posting_id')
            ->map(fn ($id) => (int) $id)
            ->all();

        return view('candidate.jobs.index', [
            'jobs' => $jobs,
            'filters' => $filters,
            'categories' => $this->search->categories(),
            'jobTypes' => $this->search->jobTypeOptions(forCurrentDomain: false),
            'experienceLevels' => $this->search->experienceOptions(forCurrentDomain: false),
            'appliedJobIds' => $appliedJobIds,
            'savedJobIds' => $savedJobIds,
            'postedWithinOptions' => [
                '1' => 'Last 24 hours',
                '7' => 'Last 7 days',
                '14' => 'Last 14 days',
                '30' => 'Last 30 days',
            ],
        ]);
    }

    public function show(Request $request, JobPosting $job): View|RedirectResponse
    {
        $this->ensureVisible($job);

        $candidate = $request->user('candidate');
        $application = $candidate->applications()
            ->where('job_posting_id', $job->id)
            ->with('resume')
            ->first();

        $isSaved = $candidate->savedJobs()
            ->where('job_posting_id', $job->id)
            ->exists();

        return view('candidate.jobs.show', [
            'job' => $job->load(['employer', 'category']),
            'application' => $application,
            'isSaved' => $isSaved,
            'resumes' => $candidate->resumes()->latest('id')->get(),
            'defaultResumeId' => $candidate->defaultResume()?->id,
        ]);
    }

    public function apply(ApplyJobRequest $request, JobPosting $job): RedirectResponse
    {
        $this->ensureVisible($job);

        $candidate = $request->user('candidate');
        $resume = $candidate->resumes()->whereKey($request->integer('candidate_resume_id'))->firstOrFail();

        $this->applications->apply(
            $candidate,
            $job,
            $resume,
            $request->filled('cover_letter') ? $request->string('cover_letter')->trim()->value() : null,
        );

        return redirect()
            ->route('candidate.applications.index')
            ->with('success', 'Your application has been submitted.');
    }

    private function ensureVisible(JobPosting $job): void
    {
        $visible = JobPosting::query()
            ->available()
            ->whereKey($job->id)
            ->exists();

        // Allow viewing already-applied jobs even if they closed later.
        if (! $visible) {
            $applied = auth('candidate')->user()?->applications()
                ->where('job_posting_id', $job->id)
                ->exists();

            abort_unless($applied, 404);
        }
    }
}
