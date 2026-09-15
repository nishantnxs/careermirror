@extends('layouts.marketing')

@section('title', $job->title)

@section('content')
<section class="latest-openings" style="padding-top: 48px; padding-bottom: 60px;">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-8">
                <div class="job-card p-4 mb-3">
                    <div class="d-flex justify-content-between align-items-start mb-3 gap-2">
                        <div>
                            <h1 class="h3 mb-2" style="color:#071d35;font-family:'Space Grotesk',sans-serif;">{{ $job->title }}</h1>
                            <div class="job-info d-flex flex-wrap gap-3">
                                <span><i class="fa-regular fa-building me-1"></i>{{ $job->employer?->company_name ?? 'Employer' }}</span>
                                @if ($job->location)
                                    <span><i class="fa-solid fa-location-dot me-1"></i>{{ $job->location }}</span>
                                @endif
                                <span><i class="fa-solid fa-wallet me-1"></i>{{ $job->salaryLabel() }}</span>
                            </div>
                        </div>
                        <span class="job-time">{{ $job->published_at?->diffForHumans() }}</span>
                    </div>
                    <div class="job-tags d-flex flex-wrap gap-2 mb-4">
                        @if ($job->job_type)
                            <span class="tag tag-light">{{ ucwords(str_replace(['-', '_'], ' ', $job->job_type)) }}</span>
                        @endif
                        @if ($job->experience_level)
                            <span class="tag">{{ ucwords(str_replace(['-', '_'], ' ', $job->experience_level)) }}</span>
                        @endif
                        @if ($job->category)
                            <span class="tag tag-blue">{{ $job->category->name }}</span>
                        @endif
                    </div>
                    <div style="white-space: pre-wrap; color:#071d35;">{{ $job->description }}</div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="career-feature-card">
                    <span class="career-feature-label">Ready to apply?</span>
                    <h3>Submit your application</h3>
                    <p class="mb-3" style="color:#5a6c79;">Sign in as a candidate to apply with your resume and track status.</p>
                    @auth('candidate')
                        <a href="{{ route('candidate.jobs.show', $job) }}" class="career-feature-link">Continue to apply <span>→</span></a>
                    @else
                        <a href="{{ route('candidate.login') }}" class="career-feature-link">Sign in to apply <span>→</span></a>
                        <div class="mt-2">
                            <a href="{{ route('candidate.register') }}" class="career-feature-link">Create candidate account <span>→</span></a>
                        </div>
                    @endauth
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
