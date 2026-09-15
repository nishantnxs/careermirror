@extends('layouts.marketing')

@section('title', 'Find your next role')

@section('content')
@php
    $selectedId = (int) ($selectedJobId ?? ($jobs->first()?->id ?? 0));
    $jobTypesFixed = [
        'full-time' => 'Full-time',
        'part-time' => 'Part-time',
        'contract' => 'Contract',
        'internship' => 'Internship',
        'freelance' => 'Freelance',
    ];
@endphp

<section class="jobs-section">
    <div class="container">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
            <div>
                <h1 class="h3 fw-semibold mb-1">Find your next role</h1>
                <p class="mb-0">{{ $jobs->total() }} open position{{ $jobs->total() === 1 ? '' : 's' }}</p>
            </div>
            @auth('candidate')
                <a href="{{ route('candidate.saved-jobs.index') }}" class="btn btn-outline-secondary">Saved jobs</a>
            @endauth
        </div>

        <form method="GET" action="{{ route('jobs.index') }}" class="job-filters mb-5" id="jobFiltersForm">
            @if ($selectedId)
                <input type="hidden" name="job" value="{{ $selectedId }}">
            @endif
            <div class="row g-3">
                <div class="col-lg-8">
                    <div class="search-box position-relative">
                        <i class="fas fa-search"></i>
                        <input type="text" name="keyword" class="form-control"
                               placeholder="Job title, company or city"
                               value="{{ $filters['keyword'] ?? '' }}">
                    </div>
                </div>
                <div class="col-lg-2">
                    <select name="category_id" class="form-select" onchange="this.form.submit()">
                        <option value="">All categories</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" @selected((string) ($filters['category_id'] ?? '') === (string) $category->id)>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-2">
                    <select name="job_type" class="form-select" onchange="this.form.submit()">
                        <option value="">All types</option>
                        @foreach ($jobTypesFixed as $value => $label)
                            <option value="{{ $value }}" @selected(($filters['job_type'] ?? '') === $value)>{{ $label }}</option>
                        @endforeach
                        @foreach ($jobTypes as $value => $label)
                            @continue(isset($jobTypesFixed[$value]))
                            <option value="{{ $value }}" @selected(($filters['job_type'] ?? '') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </form>

        <div class="job-container">
            <div class="row g-4">
                <div class="col-lg-4">
                    <div class="job-list">
                        @forelse ($jobs as $job)
                            <div class="candidate-job-card {{ $job->id === $selectedId ? 'active' : '' }}"
                                 data-job="job-{{ $job->id }}"
                                 role="button"
                                 tabindex="0">
                                <div class="d-flex justify-content-between align-items-start mb-2 gap-2">
                                    <h3 class="job-title">{{ $job->title }}</h3>
                                    <span class="job-date">{{ $job->published_at?->diffForHumans() ?? $job->created_at->diffForHumans() }}</span>
                                </div>
                                <ul class="job-info list-unstyled mb-0">
                                    <li><i class="fa-regular fa-building"></i>{{ $job->employer?->company_name ?? 'Employer' }}</li>
                                    @if ($job->location)
                                        <li><i class="fa-solid fa-location-dot"></i>{{ $job->location }}</li>
                                    @endif
                                    <li><i class="fa-solid fa-wallet"></i>{{ $job->salaryLabel() }}</li>
                                </ul>
                                <div class="job-tags mt-3">
                                    @if ($job->job_type)
                                        <span>{{ ucwords(str_replace(['-', '_'], ' ', $job->job_type)) }}</span>
                                    @endif
                                    @if ($job->category)
                                        <span>{{ $job->category->name }}</span>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="candidate-job-card">
                                <p class="mb-0 text-secondary">No open positions match your filters.</p>
                            </div>
                        @endforelse
                    </div>

                    @if ($jobs->hasPages())
                        <div class="mt-3">{{ $jobs->links() }}</div>
                    @endif
                </div>

                <div class="col-lg-8">
                    <div class="job-details">
                        @forelse ($jobs as $job)
                            <div class="job-content {{ $job->id === $selectedId ? 'active' : '' }}" id="job-{{ $job->id }}">
                                <div class="detail-card mb-4">
                                    <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                                        <h2 class="h4 fw-semibold mb-0">{{ $job->title }}</h2>
                                        @auth('candidate')
                                            @php($isSaved = in_array($job->id, $savedJobIds ?? [], true))
                                            <form method="POST" action="{{ $isSaved ? route('candidate.jobs.unsave', $job) : route('candidate.jobs.save', $job) }}">
                                                @csrf
                                                @if ($isSaved)
                                                    @method('DELETE')
                                                @endif
                                                <button type="submit"
                                                        class="job-save-toggle{{ $isSaved ? ' is-saved' : '' }}"
                                                        aria-label="{{ $isSaved ? 'Remove from saved jobs' : 'Save job' }}"
                                                        title="{{ $isSaved ? 'Saved' : 'Save job' }}">
                                                    <i class="fa-{{ $isSaved ? 'solid' : 'regular' }} fa-bookmark"></i>
                                                </button>
                                            </form>
                                        @endauth
                                    </div>
                                    <div class="detail-meta mb-3">
                                        <span><i class="fa-regular fa-building"></i>{{ $job->employer?->company_name ?? 'Employer' }}</span>
                                        @if ($job->location)
                                            <span><i class="fa-solid fa-location-dot"></i>{{ $job->location }}</span>
                                        @endif
                                        <span><i class="fa-solid fa-wallet"></i>{{ $job->salaryLabel() }}</span>
                                        <span><i class="fa-regular fa-calendar"></i>Posted {{ $job->published_at?->diffForHumans() ?? $job->created_at->diffForHumans() }}</span>
                                    </div>
                                    <div class="job-tags mb-4">
                                        @if ($job->job_type)
                                            <span>{{ ucwords(str_replace(['-', '_'], ' ', $job->job_type)) }}</span>
                                        @endif
                                        @if ($job->category)
                                            <span>{{ $job->category->name }}</span>
                                        @endif
                                        @if ($job->experience_level)
                                            <span>{{ ucwords(str_replace(['-', '_'], ' ', $job->experience_level)) }}</span>
                                        @endif
                                    </div>

                                    <div class="job-detail-actions d-flex flex-wrap align-items-center gap-2">
                                        @if (in_array($job->id, $appliedJobIds ?? [], true))
                                            <a href="{{ route('candidate.applications.index') }}" class="btn btn-outline-secondary">View application</a>
                                        @elseif (auth('candidate')->check())
                                            <button type="button"
                                                    class="btn btn-primary apply-open-btn"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#applyModal"
                                                    data-job-id="{{ $job->id }}"
                                                    data-job-title="{{ $job->title }}"
                                                    data-company="{{ $job->employer?->company_name ?? 'the employer' }}">
                                                Apply now
                                            </button>
                                        @else
                                            <a href="{{ route('candidate.login') }}" class="btn btn-primary">Apply now</a>
                                        @endif
                                    </div>
                                </div>

                                <div class="detail-card mb-4">
                                    <h3 class="h6 fw-semibold mb-3">About the role</h3>
                                    <div style="white-space: pre-wrap">{{ $job->description }}</div>
                                </div>
                            </div>
                        @empty
                            <div class="detail-card">
                                <p class="mb-0 text-secondary">Select filters or check back later for new openings.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

@auth('candidate')
    <div class="modal fade" id="applyModal" tabindex="-1" aria-labelledby="applyModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered apply-modal-dialog">
            <div class="modal-content apply-modal-content">
                <form method="POST" id="applyForm" action="#">
                    @csrf
                    <div class="modal-header apply-modal-header">
                        <div>
                            <h5 class="modal-title" id="applyModalLabel">Apply to role</h5>
                            <p class="apply-modal-subtitle mb-0">Your CareerMirror resume will be attached with this application.</p>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body apply-modal-body">
                        @if (($resumes ?? collect())->isEmpty())
                            <p class="mb-0">You need a resume before applying.
                                <a href="{{ route('candidate.resumes.create') }}">Build one now</a>.
                            </p>
                        @else
                            <div class="mb-3">
                                <label for="candidate_resume_id" class="form-label">Resume</label>
                                <select name="candidate_resume_id" id="candidate_resume_id" class="form-select" required>
                                    @foreach ($resumes as $resume)
                                        <option value="{{ $resume->id }}" @selected($resume->id === ($defaultResumeId ?? null))>
                                            {{ $resume->applicationOptionLabel() }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <textarea name="cover_letter" class="form-control apply-message"
                                      id="applyCoverLetter"
                                      placeholder="Tell the employer why you're a great fit..."
                                      rows="6"></textarea>
                        @endif
                    </div>
                    <div class="modal-footer apply-modal-footer">
                        <button type="button" class="btn btn-link cancel-btn" data-bs-dismiss="modal">Cancel</button>
                        @if (($resumes ?? collect())->isNotEmpty())
                            <button type="submit" class="btn btn-primary submit-application-btn">Submit application</button>
                        @endif
                    </div>
                </form>
            </div>
        </div>
    </div>
@endauth
@endsection

@push('scripts')
<script>
(function () {
    const cards = document.querySelectorAll('.candidate-job-card[data-job]');
    const contents = document.querySelectorAll('.job-content');
    const jobHidden = document.querySelector('#jobFiltersForm input[name="job"]');

    function showJob(jobId) {
        cards.forEach((card) => card.classList.toggle('active', card.dataset.job === jobId));
        contents.forEach((panel) => panel.classList.toggle('active', panel.id === jobId));
        if (jobHidden) {
            jobHidden.value = jobId.replace('job-', '');
        }
        const url = new URL(window.location.href);
        url.searchParams.set('job', jobId.replace('job-', ''));
        window.history.replaceState({}, '', url);
    }

    cards.forEach((card) => {
        card.addEventListener('click', () => showJob(card.dataset.job));
        card.addEventListener('keydown', (event) => {
            if (event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                showJob(card.dataset.job);
            }
        });
    });

    const applyForm = document.getElementById('applyForm');
    document.querySelectorAll('.apply-open-btn').forEach((button) => {
        button.addEventListener('click', () => {
            const jobId = button.dataset.jobId;
            const title = button.dataset.jobTitle;
            const company = button.dataset.company;
            const label = document.getElementById('applyModalLabel');
            const cover = document.getElementById('applyCoverLetter');
            if (label) {
                label.textContent = 'Apply to ' + title;
            }
            if (cover) {
                cover.placeholder = "Tell " + company + " why you're a great fit...";
            }
            if (applyForm) {
                applyForm.action = @json(url('/candidate/jobs')) + '/' + jobId + '/apply';
            }
        });
    });

    const keywordInput = document.querySelector('#jobFiltersForm input[name="keyword"]');
    let debounce;
    keywordInput?.addEventListener('keydown', (event) => {
        if (event.key === 'Enter') {
            event.preventDefault();
            document.getElementById('jobFiltersForm').submit();
        }
    });
})();
</script>
@endpush
