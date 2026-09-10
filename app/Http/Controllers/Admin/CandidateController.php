<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AccountStatus;
use App\Enums\ActivityAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCandidateRequest;
use App\Models\Candidate;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CandidateController extends Controller
{
    public function __construct(public ActivityLogger $activity) {}

    public function index(Request $request): View
    {
        $candidates = Candidate::query()
            ->search($request->string('search')->trim()->value())
            ->status($request->string('status')->toString() ?: null)
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.candidates.index', [
            'candidates' => $candidates,
            'statuses' => AccountStatus::options(),
        ]);
    }

    public function create(): View
    {
        return view('admin.candidates.create', [
            'candidate' => new Candidate(['status' => AccountStatus::Active]),
            'statuses' => AccountStatus::options(),
        ]);
    }

    public function store(StoreCandidateRequest $request): RedirectResponse
    {
        $candidate = Candidate::create($request->candidateData());

        $this->activity->record(
            ActivityAction::CandidateCreated,
            $candidate,
            null,
            $candidate->only(['name', 'email', 'phone', 'status']),
            "Candidate \"{$candidate->name}\" created.",
        );

        return redirect()->route('admin.candidates.show', $candidate)
            ->with('success', "Candidate \"{$candidate->name}\" created successfully.");
    }

    public function show(Candidate $candidate): View
    {
        return view('admin.candidates.show', [
            'candidate' => $candidate,
            'statuses' => AccountStatus::options(),
        ]);
    }

    public function updateStatus(Request $request, Candidate $candidate): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::enum(AccountStatus::class)],
        ]);

        $previous = $candidate->status;
        $candidate->update(['status' => $validated['status']]);
        $candidate = $candidate->fresh();

        $this->activity->record(
            ActivityAction::CandidateStatusChanged,
            $candidate,
            ['status' => $previous],
            ['status' => $candidate->status],
            sprintf('Candidate "%s" is now %s.', $candidate->name, $candidate->status->label()),
        );

        return back()->with('success', sprintf(
            'Candidate "%s" is now %s.',
            $candidate->name,
            $candidate->status->label(),
        ));
    }
}
