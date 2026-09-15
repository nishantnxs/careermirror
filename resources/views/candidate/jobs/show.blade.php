@extends('layouts.marketing')

@section('title', $job->title)

@section('content')
<section class="candidate-panel">
    <div class="container">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
            <div>
                <h1 class="panel-title">{{ $job->title }}</h1>
                <p class="panel-subtitle">
                    {{ $job->employer?->company_name ?? 'Employer' }}
                    @if ($job->location) · {{ $job->location }} @endif
                    @if ($job->published_at) · Posted {{ $job->published_at->diffForHumans() }} @endif
                </p>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                @if ($isSaved)
                    <form method="POST" action="{{ route('candidate.jobs.unsave', $job) }}">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-secondary">Unsave</button>
                    </form>
                @else
                    <form method="POST" action="{{ route('candidate.jobs.save', $job) }}">
                        @csrf
                        <button type="submit" class="btn btn-outline-secondary">Save job</button>
                    </form>
                @endif
                <a href="{{ route('candidate.jobs.index') }}" class="btn btn-outline-secondary">Back to jobs</a>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-lg-8">
                <div class="panel-card mb-3">
                    <div class="panel-card-header">Job details</div>
                    <div class="panel-card-body">
                        <div class="d-flex flex-wrap gap-2 mb-3">
                            @if ($job->category)
                                <span class="badge bg-light text-dark border">{{ $job->category->name }}</span>
                            @endif
                            @if ($job->job_type)
                                <span class="badge bg-light text-dark border">{{ ucwords(str_replace(['-', '_'], ' ', $job->job_type)) }}</span>
                            @endif
                            @if ($job->experience_level)
                                <span class="badge bg-light text-dark border">{{ ucwords(str_replace(['-', '_'], ' ', $job->experience_level)) }}</span>
                            @endif
                            <span class="badge bg-light text-dark border">{{ $job->salaryLabel() }}</span>
                        </div>
                        <div style="white-space: pre-wrap">{{ $job->description }}</div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="panel-card mb-3">
                    <div class="panel-card-header">Apply</div>
                    <div class="panel-card-body">
                        @if ($application && $application->status->value !== 'withdrawn')
                            <p class="mb-2">
                                Status:
                                <span class="badge {{ $application->status->badgeClass() }}">{{ $application->status->label() }}</span>
                            </p>
                            <p class="small text-secondary">
                                Applied {{ $application->applied_at->format('d M Y, h:i A') }}
                                @if ($application->resume)
                                    with resume “{{ $application->resume->title }}”.
                                @endif
                            </p>
                            <a href="{{ route('candidate.applications.show', $application) }}" class="btn btn-primary w-100">
                                View application
                            </a>
                        @elseif (! $job->isAvailable())
                            <p class="text-secondary small mb-0">This job is no longer accepting applications.</p>
                        @elseif ($resumes->isEmpty())
                            <p class="text-secondary small">Add a resume before applying.</p>
                            <a href="{{ route('candidate.resumes.create') }}" class="btn btn-primary w-100">Build resume</a>
                        @else
                            <form method="POST" action="{{ route('candidate.jobs.apply', $job) }}">
                                @csrf
                                <div class="mb-3">
                                    <label for="candidate_resume_id" class="form-label required">Resume</label>
                                    <select name="candidate_resume_id" id="candidate_resume_id"
                                            class="form-select @error('candidate_resume_id') is-invalid @enderror" required>
                                        @foreach ($resumes as $resume)
                                            <option value="{{ $resume->id }}"
                                                @selected((string) old('candidate_resume_id', $defaultResumeId) === (string) $resume->id)>
                                                {{ $resume->applicationOptionLabel() }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('candidate_resume_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="mb-3">
                                    <label for="cover_letter" class="form-label">Cover letter (optional)</label>
                                    <textarea name="cover_letter" id="cover_letter" rows="4"
                                              class="form-control @error('cover_letter') is-invalid @enderror">{{ old('cover_letter') }}</textarea>
                                    @error('cover_letter') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                @error('job') <div class="text-danger small mb-3">{{ $message }}</div> @enderror
                                <button type="submit" class="btn btn-primary w-100">Submit application</button>
                            </form>
                        @endif
                    </div>
                </div>

                <div class="panel-card">
                    <div class="panel-card-header">Company</div>
                    <div class="panel-card-body">
                        <div class="fw-semibold">{{ $job->employer?->company_name ?? '—' }}</div>
                        @if ($job->expires_at)
                            <div class="small text-secondary mt-2">Applies until {{ $job->expires_at->format('d M Y') }}</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
