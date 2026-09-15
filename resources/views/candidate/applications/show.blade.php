@extends('layouts.marketing')

@section('title', 'Application')

@section('content')
@php($job = $application->jobPosting)

<section class="candidate-panel">
    <div class="container">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
            <div>
                <h1 class="panel-title">{{ $job?->title ?? 'Application' }}</h1>
                <p class="panel-subtitle">
                    {{ $job?->employer?->company_name }}
                    · Applied {{ $application->applied_at->format('d M Y, h:i A') }}
                </p>
            </div>
            <a href="{{ route('candidate.applications.index') }}" class="btn btn-outline-secondary">Back</a>
        </div>

        <div class="row g-3">
            <div class="col-lg-8">
                <div class="panel-card mb-3">
                    <div class="panel-card-header">Application progress</div>
                    <div class="panel-card-body">
                        <p class="mb-3">
                            Current status:
                            <span class="badge {{ $application->status->badgeClass() }}">{{ $application->status->label() }}</span>
                        </p>
                        <ol class="mb-0">
                            @foreach (\App\Enums\ApplicationStatus::cases() as $status)
                                @if ($status === \App\Enums\ApplicationStatus::Withdrawn)
                                    @continue
                                @endif
                                <li class="mb-1 {{ $application->status === $status ? 'fw-semibold' : 'text-secondary' }}">
                                    {{ $status->label() }}
                                </li>
                            @endforeach
                        </ol>
                        @if ($application->status === \App\Enums\ApplicationStatus::Withdrawn)
                            <p class="text-secondary small mt-3 mb-0">
                                You withdrew this application on {{ $application->withdrawn_at?->format('d M Y') }}.
                            </p>
                        @endif
                    </div>
                </div>

                @if ($job)
                    <div class="panel-card">
                        <div class="panel-card-header">Job summary</div>
                        <div class="panel-card-body">
                            <div class="mb-2 text-secondary small">{{ $job->location }} · {{ $job->salaryLabel() }}</div>
                            <div style="white-space: pre-wrap">{{ \Illuminate\Support\Str::limit($job->description, 600) }}</div>
                            @if ($job->isAvailable())
                                <a href="{{ route('candidate.jobs.show', $job) }}" class="btn btn-outline-secondary btn-sm mt-3">Open job</a>
                            @endif
                        </div>
                    </div>
                @endif
            </div>

            <div class="col-lg-4">
                <div class="panel-card mb-3">
                    <div class="panel-card-header">Submitted resume</div>
                    <div class="panel-card-body">
                        @if ($application->resume)
                            <div class="fw-semibold mb-2">{{ $application->resume->title }}</div>
                            <div class="small text-secondary mb-3">{{ $application->resume->source->label() }}</div>
                            <a href="{{ route('candidate.resumes.preview', $application->resume) }}" class="btn btn-outline-secondary w-100">
                                View resume
                            </a>
                        @else
                            <p class="text-secondary small mb-0">Resume no longer available.</p>
                        @endif
                    </div>
                </div>

                @if ($application->cover_letter)
                    <div class="panel-card mb-3">
                        <div class="panel-card-header">Cover letter</div>
                        <div class="panel-card-body" style="white-space: pre-wrap">{{ $application->cover_letter }}</div>
                    </div>
                @endif

                @if ($application->canBeWithdrawn())
                    <form method="POST" action="{{ route('candidate.applications.withdraw', $application) }}"
                          onsubmit="return confirm('Withdraw this application?')">
                        @csrf
                        <button type="submit" class="btn btn-outline-danger w-100">Withdraw application</button>
                    </form>
                @endif
            </div>
        </div>
    </div>
</section>
@endsection
