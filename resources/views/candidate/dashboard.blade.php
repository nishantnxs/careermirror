@extends('layouts.marketing')

@section('title', 'Dashboard')

@section('content')
<section class="dashboard-section">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="dashboard-header mb-4">
                    <h1 class="mb-2">Welcome back, {{ $firstName }}</h1>
                    <p class="mb-0">Here's your job search at a glance.</p>
                </div>
                <div class="dashboard-actions d-flex flex-wrap align-items-center gap-2 mb-4">
                    <a href="{{ route('candidate.applications.index') }}" class="btn btn-primary">All applications</a>
                    <a href="{{ route('jobs.index') }}" class="btn btn-outline-secondary">Browse jobs</a>
                    @if ($hasResume)
                        <a href="{{ route('candidate.resumes.index') }}" class="btn edit-resume-link">My resumes</a>
                    @else
                        <a href="{{ route('candidate.resumes.create') }}" class="btn edit-resume-link">Create resume</a>
                    @endif
                </div>
                <div class="recent-applications">
                    <div class="recent-applications-header">
                        <h2 class="mb-0">Recent applications</h2>
                    </div>
                    <div class="recent-applications-body">
                        @forelse ($recentApplications as $application)
                            @php($job = $application->jobPosting)
                            <a href="{{ route('candidate.applications.show', $application) }}"
                               class="application-card d-flex align-items-center justify-content-between text-decoration-none {{ $loop->last ? '' : 'mb-3' }}">
                                <div class="application-info">
                                    <h3 class="application-title mb-2">{{ $job?->title ?? 'Job removed' }}</h3>
                                    <p class="application-meta mb-0">
                                        {{ collect([
                                            $job?->employer?->company_name,
                                            $job?->location,
                                            $application->applied_at ? 'applied '.$application->applied_at->diffForHumans() : null,
                                        ])->filter()->implode(' · ') }}
                                    </p>
                                </div>
                                <div class="application-status">
                                    <span class="status-badge">{{ $application->status->badgeLabel() }}</span>
                                </div>
                            </a>
                        @empty
                            <p class="mb-0">No applications yet — find a role that fits and apply in one click.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
