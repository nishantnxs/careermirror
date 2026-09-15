@extends('layouts.marketing')

@section('title', $application->candidate?->name ?? 'Applicant')

@section('content')
@php
    $candidate = $application->candidate;
    $job = $application->jobPosting;
    $resume = $application->resume;
@endphp

<section class="employer-panel">
    <div class="container">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
            <div>
                <h1 class="panel-title">{{ $candidate?->name ?? 'Applicant' }}</h1>
                <p class="panel-subtitle">
                    Applied to {{ $job?->title ?? 'a job' }}
                    @if ($application->applied_at)
                        · {{ $application->applied_at->format('d M Y, h:i A') }}
                    @endif
                </p>
            </div>
            <a href="{{ route('employer.applicants.index', array_filter(['job' => $job?->id])) }}" class="btn btn-outline-secondary">
                Applications received
            </a>
        </div>

        <div class="row g-3">
            <div class="col-lg-8">
                <div class="panel-card mb-3">
                    <div class="panel-card-header">Candidate</div>
                    <div class="panel-card-body">
                        <dl class="row mb-0 small">
                            <dt class="col-sm-4 text-secondary fw-normal">Name</dt>
                            <dd class="col-sm-8">{{ $candidate?->name ?? '—' }}</dd>
                            <dt class="col-sm-4 text-secondary fw-normal">Email</dt>
                            <dd class="col-sm-8">{{ $candidate?->email ?? '—' }}</dd>
                            <dt class="col-sm-4 text-secondary fw-normal">Phone</dt>
                            <dd class="col-sm-8">{{ $candidate?->phone ?? '—' }}</dd>
                            <dt class="col-sm-4 text-secondary fw-normal">Location</dt>
                            <dd class="col-sm-8">{{ $candidate?->location ?? '—' }}</dd>
                            <dt class="col-sm-4 text-secondary fw-normal">Current title</dt>
                            <dd class="col-sm-8 mb-0">{{ $candidate?->current_title ?? $candidate?->headline ?? '—' }}</dd>
                        </dl>
                    </div>
                </div>

                <div class="panel-card mb-3">
                    <div class="panel-card-header">Submitted resume</div>
                    <div class="panel-card-body">
                        @if ($resume)
                            <div class="fw-semibold mb-1">{{ $resume->title }}</div>
                            <div class="small text-secondary mb-3">{{ $resume->source->label() }}</div>
                            <div class="d-flex flex-wrap gap-2">
                                <a href="{{ route('employer.applicants.resume', $application) }}" class="btn btn-primary">
                                    View resume
                                </a>
                                @if ($resume->isUploaded())
                                    <a href="{{ route('employer.applicants.resume.download', $application) }}" class="btn btn-outline-secondary">
                                        Download file
                                    </a>
                                @endif
                            </div>
                        @else
                            <p class="text-secondary small mb-0">Resume is no longer available.</p>
                        @endif
                    </div>
                </div>

                @if ($application->cover_letter)
                    <div class="panel-card">
                        <div class="panel-card-header">Cover letter</div>
                        <div class="panel-card-body" style="white-space: pre-wrap">{{ $application->cover_letter }}</div>
                    </div>
                @endif
            </div>

            <div class="col-lg-4">
                <div class="panel-card mb-3">
                    <div class="panel-card-header">Application status</div>
                    <div class="panel-card-body">
                        <p class="mb-3">
                            Current:
                            <span class="badge {{ $application->status->badgeClass() }}">{{ $application->status->label() }}</span>
                        </p>
                        @if ($application->status === \App\Enums\ApplicationStatus::Withdrawn)
                            <p class="text-secondary small mb-0">
                                This candidate withdrew on {{ $application->withdrawn_at?->format('d M Y') }}.
                            </p>
                        @else
                            <form method="POST" action="{{ route('employer.applicants.status', $application) }}">
                                @csrf
                                @method('PATCH')
                                <label for="status" class="form-label">Update status</label>
                                <select name="status" id="status" class="form-select @error('status') is-invalid @enderror">
                                    @foreach (\App\Enums\ApplicationStatus::cases() as $status)
                                        @if ($status === \App\Enums\ApplicationStatus::Withdrawn)
                                            @continue
                                        @endif
                                        <option value="{{ $status->value }}" @selected($application->status === $status)>
                                            {{ $status->label() }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                <button type="submit" class="btn btn-primary w-100 mt-3">Save status</button>
                            </form>
                        @endif
                    </div>
                </div>

                <div class="panel-card">
                    <div class="panel-card-header">Next step</div>
                    <div class="panel-card-body">
                        <a href="{{ route('employer.messages.create', ['candidate_id' => $candidate?->id, 'job_application_id' => $application->id]) }}"
                           class="btn btn-outline-secondary w-100">
                            Message candidate
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
