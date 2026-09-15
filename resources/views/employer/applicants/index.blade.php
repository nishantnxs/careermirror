@extends('layouts.marketing')

@section('title', 'Applications received')

@section('content')
<section class="applications-section">
    <div class="container">
        <div class="applications-wrapper">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
                <h2 class="applications-title mb-0">Applications received</h2>
                @if ($jobs->isNotEmpty())
                    <form method="GET" action="{{ route('employer.applicants.index') }}" class="applications-job-filter">
                        @if ($currentStatus !== '')
                            <input type="hidden" name="status" value="{{ $currentStatus }}">
                        @endif
                        <label for="job" class="visually-hidden">Job</label>
                        <select name="job" id="job" class="application-status" onchange="this.form.submit()">
                            <option value="">All jobs</option>
                            @foreach ($jobs as $job)
                                <option value="{{ $job->id }}" @selected($currentJobId === $job->id)>{{ $job->title }}</option>
                            @endforeach
                        </select>
                    </form>
                @endif
            </div>

            @if (session('success'))
                <div class="alert alert-success py-2 small mb-3">{{ session('success') }}</div>
            @endif

            @forelse ($applications as $application)
                @php
                    $candidate = $application->candidate;
                    $job = $application->jobPosting;
                    $professionalTitle = $candidate?->current_title ?: $candidate?->headline;
                    $contact = collect([
                        $candidate?->email,
                        $candidate?->location,
                    ])->filter()->implode(' · ');
                @endphp
                <div class="application-card {{ $loop->last ? '' : 'mb-3' }}">
                    <div class="row align-items-center">
                        <div class="col-lg-7 col-md-7">
                            <div class="applicant-info">
                                <h5 class="applicant-name mb-1">{{ $candidate?->name ?? 'Candidate' }}</h5>
                                @if ($professionalTitle)
                                    <p class="job-title mb-1">{{ $professionalTitle }}</p>
                                @endif
                                <p class="application-meta mb-1">
                                    {{ collect([
                                        $job?->title ?? 'Job removed',
                                        $application->applied_at ? 'applied '.$application->applied_at->diffForHumans() : null,
                                    ])->filter()->implode(' · ') }}
                                </p>
                                @if ($contact !== '')
                                    <p class="applicant-contact mb-0">{{ $contact }}</p>
                                @endif
                            </div>
                        </div>
                        <div class="col-lg-5 col-md-5">
                            <div class="application-actions">
                                @if ($application->status === \App\Enums\ApplicationStatus::Withdrawn)
                                    <select class="application-status" disabled>
                                        <option selected>Withdrawn</option>
                                    </select>
                                @else
                                    <form method="POST" action="{{ route('employer.applicants.status', $application) }}">
                                        @csrf
                                        @method('PATCH')
                                        <label for="status-{{ $application->id }}" class="visually-hidden">Status</label>
                                        <select name="status" id="status-{{ $application->id }}" class="application-status" onchange="this.form.submit()">
                                            @foreach (\App\Enums\ApplicationStatus::cases() as $status)
                                                @if ($status === \App\Enums\ApplicationStatus::Withdrawn)
                                                    @continue
                                                @endif
                                                <option value="{{ $status->value }}" @selected($application->status === $status)>
                                                    {{ $status->label() }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </form>
                                @endif
                                <a href="{{ route('employer.applicants.show', $application) }}" class="view-resume-btn">View resume &amp; chat</a>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="application-card">
                    <p class="application-meta mb-0">No applicants yet. When candidates apply, they will appear here.</p>
                </div>
            @endforelse

            @if ($applications->hasPages())
                <div class="mt-4">{{ $applications->links() }}</div>
            @endif
        </div>
    </div>
</section>
@endsection
