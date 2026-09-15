<?php

namespace App\Http\Controllers\Candidate;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $candidate = $request->user('candidate');

        $hasResume = $candidate->resumes()->exists();

        $recentApplications = $candidate->applications()
            ->with(['jobPosting.employer'])
            ->latest('applied_at')
            ->limit(5)
            ->get();

        $firstName = str($candidate->name)->before(' ')->toString() ?: $candidate->name;

        return view('candidate.dashboard', [
            'firstName' => $firstName,
            'hasResume' => $hasResume,
            'recentApplications' => $recentApplications,
        ]);
    }
}
