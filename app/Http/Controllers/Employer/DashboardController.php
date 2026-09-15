<?php

namespace App\Http\Controllers\Employer;

use App\Http\Controllers\Controller;
use App\Models\JobApplication;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $employer = $request->user('employer');

        $jobPostingsCount = $employer->jobPostings()->count();

        $applicationsReceived = JobApplication::query()
            ->whereHas('jobPosting', fn ($query) => $query->where('employer_id', $employer->id))
            ->count();

        $subscription = $employer->currentSubscription()?->load('plan');

        $currentPlanName = $subscription?->plan?->title ?? 'None';

        $jobPostings = $employer->jobPostings()
            ->withCount('applications')
            ->latest()
            ->limit(10)
            ->get();

        $firstName = str($employer->name)->before(' ')->toString() ?: $employer->name;

        return view('employer.dashboard', [
            'firstName' => $firstName,
            'jobPostingsCount' => $jobPostingsCount,
            'applicationsReceived' => $applicationsReceived,
            'currentPlanName' => $currentPlanName,
            'jobPostings' => $jobPostings,
        ]);
    }
}
