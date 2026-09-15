@extends('layouts.marketing')

@section('title', 'Dashboard')

@section('content')
<section class="dashboard-section">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="dashboard-header mb-4">
                    <h1 class="mb-2">Welcome back, {{ $firstName }}</h1>
                    <p class="mb-0">Here's how your hiring is going.</p>
                </div>
                <div class="row g-3 mb-5">
                    <div class="col-md-4">
                        <div class="dashboard-stat-card h-100">
                            <div class="stat-icon"><i class="fa-regular fa-file-lines"></i></div>
                            <h3>{{ $jobPostingsCount }}</h3>
                            <p>Job postings</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="dashboard-stat-card h-100">
                            <div class="stat-icon"><i class="fa-regular fa-folder-open"></i></div>
                            <h3>{{ $applicationsReceived }}</h3>
                            <p>Applications received</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="dashboard-stat-card h-100">
                            <div class="stat-icon"><i class="fa-regular fa-user"></i></div>
                            <h3>{{ $currentPlanName }}</h3>
                            <p>Current plan</p>
                        </div>
                    </div>
                </div>
                <div class="dashboard-actions d-flex flex-wrap align-items-center gap-2 mb-4">
                    <a href="{{ route('employer.jobs.create') }}" class="btn btn-primary">Post a job</a>
                    <a href="{{ route('employer.applicants.index') }}" class="btn btn-outline-secondary">Review applications</a>
                    <a href="{{ route('employer.plans.index') }}" class="btn all-applications-link">Change plan</a>
                </div>
                <div class="job-postings-card">
                    <div class="job-postings-header">
                        <h5 class="mb-0">Your job postings</h5>
                    </div>
                    @forelse ($jobPostings as $job)
                        @php
                            $postedAt = $job->published_at ?? $job->created_at;
                            $postingMeta = collect([
                                $job->location ?: null,
                                $job->salaryLabel(),
                                $postedAt?->diffForHumans(),
                            ])->filter()->implode(' · ');
                        @endphp
                        <div class="job-posting-item">
                            <div class="job-info">
                                <h6 class="mb-1">
                                    <a href="{{ route('employer.jobs.edit', $job) }}">{{ $job->title }}</a>
                                </h6>
                                <p class="mb-0">{{ $postingMeta }}</p>
                            </div>
                            <div class="job-actions">
                                <span class="job-status">{{ $job->status->value }}</span>
                                <span class="applicants">
                                    {{ $job->applications_count }} {{ Str::plural('applicant', $job->applications_count) }}
                                </span>
                                <a href="{{ route('employer.applicants.index', ['job' => $job->id]) }}" class="btn btn-view">View</a>
                            </div>
                        </div>
                    @empty
                        <div class="job-postings-empty">
                            <p class="mb-0">No job postings yet — post a role to start hiring.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
