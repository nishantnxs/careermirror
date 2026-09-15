<?php

namespace App\Http\Controllers\Employer;

use App\Enums\ApplicationStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Employer\UpdateApplicantStatusRequest;
use App\Models\JobApplication;
use App\Services\JobApplicationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ApplicantController extends Controller
{
    public function __construct(public JobApplicationService $applications) {}

    public function index(Request $request): View
    {
        $employer = $request->user('employer');
        $status = $request->string('status')->toString();
        $jobId = $request->integer('job');

        $jobs = $employer->jobPostings()
            ->orderByDesc('id')
            ->get(['id', 'title']);

        $applications = JobApplication::query()
            ->whereHas('jobPosting', fn ($query) => $query->where('employer_id', $employer->id))
            ->with(['candidate', 'jobPosting', 'resume'])
            ->when(
                $status !== '' && ApplicationStatus::tryFrom($status),
                fn ($query) => $query->where('status', $status)
            )
            ->when($jobId > 0, fn ($query) => $query->where('job_posting_id', $jobId))
            ->latest('applied_at')
            ->paginate(15)
            ->withQueryString();

        return view('employer.applicants.index', [
            'applications' => $applications,
            'jobs' => $jobs,
            'currentStatus' => $status,
            'currentJobId' => $jobId,
        ]);
    }

    public function show(Request $request, JobApplication $application): View
    {
        $this->authorizeApplication($request, $application);

        $application->load(['candidate', 'jobPosting', 'resume']);

        return view('employer.applicants.show', [
            'application' => $application,
        ]);
    }

    public function updateStatus(UpdateApplicantStatusRequest $request, JobApplication $application): RedirectResponse
    {
        $this->authorizeApplication($request, $application);

        $this->applications->updateStatus(
            $application,
            ApplicationStatus::from($request->string('status')->toString()),
        );

        return back()->with('success', 'Application status updated.');
    }

    public function resume(Request $request, JobApplication $application): View
    {
        $this->authorizeApplication($request, $application);

        $resume = $application->resume;
        abort_unless($resume !== null, 404);

        return view('candidate.resumes.preview', [
            'resume' => $resume->load('candidate'),
            'candidate' => $resume->candidate,
            'resumeBackUrl' => route('employer.applicants.show', $application),
            'resumeBackLabel' => 'Back to applicant',
            'resumeEditUrl' => null,
            'resumeDownloadUrl' => $resume->isUploaded()
                ? route('employer.applicants.resume.download', $application)
                : null,
        ]);
    }

    public function downloadResume(Request $request, JobApplication $application): StreamedResponse
    {
        $this->authorizeApplication($request, $application);

        $resume = $application->resume;
        abort_unless($resume?->isUploaded() && $resume->file_path, 404);

        return Storage::disk('public')->download(
            $resume->file_path,
            $resume->original_filename ?: 'resume',
        );
    }

    private function authorizeApplication(Request $request, JobApplication $application): void
    {
        $application->loadMissing('jobPosting');

        abort_unless($application->jobPosting?->employer_id === $request->user('employer')->id, 404);
    }
}
