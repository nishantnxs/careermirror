<?php

namespace App\Http\Controllers\Candidate;

use App\Enums\ApplicationStatus;
use App\Http\Controllers\Controller;
use App\Models\JobApplication;
use App\Services\JobApplicationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class JobApplicationController extends Controller
{
    public function __construct(public JobApplicationService $applications) {}

    public function index(Request $request): View
    {
        $candidate = $request->user('candidate');
        $status = $request->string('status')->toString();

        $applications = $candidate->applications()
            ->with(['jobPosting.employer', 'jobPosting.category', 'resume'])
            ->when(
                $status !== '' && ApplicationStatus::tryFrom($status),
                fn ($query) => $query->where('status', $status)
            )
            ->latest('applied_at')
            ->paginate(12)
            ->withQueryString();

        return view('candidate.applications.index', [
            'applications' => $applications,
            'statuses' => ApplicationStatus::options(),
            'currentStatus' => $status,
        ]);
    }

    public function show(Request $request, JobApplication $application): View
    {
        $this->authorizeApplication($request, $application);

        $application->load(['jobPosting.employer', 'jobPosting.category', 'resume']);

        return view('candidate.applications.show', [
            'application' => $application,
        ]);
    }

    public function withdraw(Request $request, JobApplication $application): RedirectResponse
    {
        $this->authorizeApplication($request, $application);

        $this->applications->withdraw($application);

        return back()->with('success', 'Application withdrawn.');
    }

    private function authorizeApplication(Request $request, JobApplication $application): void
    {
        abort_unless($application->candidate_id === $request->user('candidate')->id, 404);
    }
}
