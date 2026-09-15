<?php

namespace App\Http\Controllers\Candidate;

use App\Http\Controllers\Controller;
use App\Models\JobPosting;
use App\Models\SavedJob;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SavedJobController extends Controller
{
    public function index(Request $request): View
    {
        $candidate = $request->user('candidate');

        $savedJobs = $candidate->savedJobs()
            ->with(['jobPosting.employer', 'jobPosting.category'])
            ->latest('id')
            ->paginate(12);

        return view('candidate.saved-jobs.index', [
            'savedJobs' => $savedJobs,
        ]);
    }

    public function store(Request $request, JobPosting $job): RedirectResponse
    {
        $visible = JobPosting::query()
            ->available()
            ->whereKey($job->id)
            ->exists();

        abort_unless($visible, 404);

        SavedJob::query()->firstOrCreate([
            'candidate_id' => $request->user('candidate')->id,
            'job_posting_id' => $job->id,
        ]);

        return back()->with('success', 'Job saved.');
    }

    public function destroy(Request $request, JobPosting $job): RedirectResponse
    {
        SavedJob::query()
            ->where('candidate_id', $request->user('candidate')->id)
            ->where('job_posting_id', $job->id)
            ->delete();

        return back()->with('success', 'Job removed from saved list.');
    }
}
