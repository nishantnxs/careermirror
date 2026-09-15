<?php

namespace App\Http\Controllers\Employer;

use App\Enums\ActivityAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Employer\JobRequest;
use App\Models\Category;
use App\Models\Domain;
use App\Models\JobPosting;
use App\Services\ActivityLogger;
use App\Services\JobPostingService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class JobController extends Controller
{
    public function index(Request $request): View
    {
        $jobs = JobPosting::query()
            ->with('category')
            ->withCount('applications')
            ->where('employer_id', $request->user('employer')->id)
            ->latest('id')
            ->paginate(10);

        $subscription = $request->user('employer')->currentSubscription();

        return view('employer.jobs.index', compact('jobs', 'subscription'));
    }

    public function create(Request $request): View|RedirectResponse
    {
        $subscription = $request->user('employer')->subscriptionAvailableForPosting();

        if ($subscription === null) {
            return redirect()
                ->route('employer.plans.index')
                ->with('error', 'Buy or activate a plan with remaining job credits before posting a job.');
        }

        return view('employer.jobs.create', [
            'job' => new JobPosting(['currency' => 'INR', 'status' => 'published']),
            'subscription' => $subscription,
            'categories' => $this->approvedCategories(),
            'availableDomains' => Domain::query()->active()->orderBy('host')->get(),
        ]);
    }

    public function store(JobRequest $request, JobPostingService $jobs): RedirectResponse
    {
        $job = $jobs->create($request->user('employer'), $request->jobData());

        return redirect()
            ->route('employer.jobs.index')
            ->with('success', "Job \"{$job->title}\" posted successfully.");
    }

    public function edit(JobPosting $job): View
    {
        abort_unless($job->employer_id === auth('employer')->id(), 404);

        return view('employer.jobs.edit', [
            'job' => $job->load(['category', 'domains']),
            'subscription' => $job->subscription,
            'categories' => $this->approvedCategories(),
            'availableDomains' => Domain::query()->active()->orderBy('host')->get(),
        ]);
    }

    public function update(JobRequest $request, JobPosting $job, JobPostingService $jobs): RedirectResponse
    {
        abort_unless($job->employer_id === auth('employer')->id(), 404);

        $job = $jobs->update($job, $request->jobData());

        return redirect()
            ->route('employer.jobs.index')
            ->with('success', "Job \"{$job->title}\" updated successfully.");
    }

    public function destroy(JobPosting $job): RedirectResponse
    {
        abort_unless($job->employer_id === auth('employer')->id(), 404);

        $title = $job->title;
        $snapshot = $job->only([
            'title', 'status', 'location', 'job_type', 'experience_level',
            'salary_min', 'salary_max', 'currency', 'expires_at', 'category_id',
        ]);

        app(ActivityLogger::class)->record(
            ActivityAction::JobDeleted,
            $job,
            $snapshot,
            null,
            "Job \"{$title}\" deleted.",
        );

        $job->delete();

        return redirect()
            ->route('employer.jobs.index')
            ->with('success', "Job \"{$title}\" deleted.");
    }

    /**
     * @return Collection<int, Category>
     */
    protected function approvedCategories()
    {
        return Category::query()->approved()->ordered()->get();
    }
}
