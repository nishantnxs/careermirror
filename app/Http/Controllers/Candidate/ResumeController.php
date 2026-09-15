<?php

namespace App\Http\Controllers\Candidate;

use App\Http\Controllers\Controller;
use App\Http\Requests\Candidate\StoreBuiltResumeRequest;
use App\Http\Requests\Candidate\UpdateBuiltResumeRequest;
use App\Http\Requests\Candidate\UploadResumeRequest;
use App\Models\CandidateResume;
use App\Services\CandidateResumeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ResumeController extends Controller
{
    public function __construct(public CandidateResumeService $resumes) {}

    public function index(Request $request): View
    {
        $candidate = $request->user('candidate');

        return view('candidate.resumes.index', [
            'resumes' => $candidate->resumes()->latest('id')->get(),
        ]);
    }

    public function create(Request $request): View
    {
        return view('candidate.resumes.create', [
            'candidate' => $request->user('candidate'),
            'resume' => new CandidateResume([
                'title' => 'My Resume',
                'content' => CandidateResume::emptyContent(),
            ]),
        ]);
    }

    public function store(StoreBuiltResumeRequest $request): RedirectResponse
    {
        $candidate = $request->user('candidate');
        $candidate->update($request->profileData());

        $resume = $this->resumes->createBuilt(
            $candidate,
            $request->string('title')->trim()->value(),
            $this->resumes->normalizeBuilderContent($request->input('content', [])),
            $request->boolean('is_default'),
        );

        return redirect()
            ->route('candidate.resumes.preview', $resume)
            ->with('success', 'Resume created successfully.');
    }

    public function uploadForm(): View
    {
        return view('candidate.resumes.upload');
    }

    public function upload(UploadResumeRequest $request): RedirectResponse
    {
        $resume = $this->resumes->storeUpload(
            $request->user('candidate'),
            $request->string('title')->trim()->value(),
            $request->file('resume_file'),
            $request->boolean('is_default'),
        );

        return redirect()
            ->route('candidate.resumes.index')
            ->with('success', 'Resume uploaded successfully.');
    }

    public function edit(Request $request, CandidateResume $resume): View|RedirectResponse
    {
        $this->authorizeResume($request, $resume);

        if ($resume->isUploaded()) {
            return redirect()
                ->route('candidate.resumes.index')
                ->with('error', 'Uploaded resumes cannot be edited in the builder. Upload a new file instead.');
        }

        return view('candidate.resumes.edit', [
            'candidate' => $request->user('candidate'),
            'resume' => $resume,
        ]);
    }

    public function update(UpdateBuiltResumeRequest $request, CandidateResume $resume): RedirectResponse
    {
        $this->authorizeResume($request, $resume);

        abort_unless($resume->isBuilt(), 404);

        $candidate = $request->user('candidate');
        $candidate->update($request->profileData());

        $this->resumes->updateBuilt(
            $resume,
            $request->string('title')->trim()->value(),
            $this->resumes->normalizeBuilderContent($request->input('content', [])),
        );

        if ($request->boolean('is_default')) {
            $this->resumes->makeDefault($resume);
        }

        return redirect()
            ->route('candidate.resumes.preview', $resume)
            ->with('success', 'Resume updated successfully.');
    }

    public function preview(Request $request, CandidateResume $resume): View
    {
        $this->authorizeResume($request, $resume);

        return view('candidate.resumes.preview', [
            'resume' => $resume->load('candidate'),
            'candidate' => $request->user('candidate'),
        ]);
    }

    public function download(Request $request, CandidateResume $resume): StreamedResponse
    {
        $this->authorizeResume($request, $resume);

        abort_unless($resume->isUploaded() && $resume->file_path, 404);

        return Storage::disk('public')->download(
            $resume->file_path,
            $resume->original_filename ?: 'resume',
        );
    }

    public function makeDefault(Request $request, CandidateResume $resume): RedirectResponse
    {
        $this->authorizeResume($request, $resume);
        $this->resumes->makeDefault($resume);

        return back()->with('success', 'Default resume updated. This resume will be preferred when applying for jobs.');
    }

    public function destroy(Request $request, CandidateResume $resume): RedirectResponse
    {
        $this->authorizeResume($request, $resume);
        $this->resumes->delete($resume);

        return redirect()
            ->route('candidate.resumes.index')
            ->with('success', 'Resume deleted.');
    }

    private function authorizeResume(Request $request, CandidateResume $resume): void
    {
        abort_unless($resume->candidate_id === $request->user('candidate')->id, 404);
    }
}
