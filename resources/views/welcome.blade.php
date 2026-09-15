@extends('layouts.marketing')

@section('title', 'See the job market clearly')

@section('content')
<section class="banner">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <div class="banner-content">
                    <span class="tagline text-white"><i class="fa-solid fa-wand-magic-sparkles"></i>Built for Canadian hiring</span>
                    <h1 class="text-white">See the job market clearly.</h1>
                    <p class="text-white">
                        {{ $siteName }} brings employers and candidates together — post roles, build resumes,
                        track every application and message each other without leaving the platform.
                    </p>
                    <form method="GET" action="{{ route('jobs.index') }}">
                        <div class="form-group">
                            <i class="fas fa-search"></i>
                            <input type="text" name="keyword" placeholder="Search roles, companies, cities" value="{{ request('keyword') }}">
                        </div>
                        <button type="submit">Search jobs</button>
                    </form>
                    <div class="d-flex banner-button">
                        <a href="{{ route('employer.register') }}">I'm hiring</a>
                        <a href="{{ route('candidate.register') }}">I'm looking for work</a>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 mt-4 mt-lg-0">
                <div class="banner-image">
                    <img src="{{ asset('design/images/careermirror-hero-team.jpg') }}" class="img-fluid rounded-4" alt="{{ $siteName }} team">
                </div>
            </div>
        </div>
    </div>
</section>

<section class="employer-candidate-section">
    <div class="container">
        <div class="row g-4">
            <div class="col-md-6">
                <div class="career-feature-card h-100">
                    <span class="career-feature-label">FOR EMPLOYERS</span>
                    <h3>Hire without the spreadsheet chaos</h3>
                    <ul class="career-feature-list">
                        <li><span class="career-feature-icon"><i class="fa-solid fa-briefcase"></i></span>Post and manage unlimited job listings by plan</li>
                        <li><span class="career-feature-icon"><i class="fa-solid fa-users"></i></span>See every applicant and their resume in one pipeline</li>
                        <li><span class="career-feature-icon"><i class="fa-solid fa-chart-column"></i></span>Track status from submitted to hired</li>
                    </ul>
                    <a href="{{ route('employer.plans.index') }}" class="career-feature-link">See employer plans <span>→</span></a>
                </div>
            </div>
            <div class="col-md-6">
                <div class="career-feature-card h-100">
                    <span class="career-feature-label">For candidates</span>
                    <h3>Apply once, follow everything</h3>
                    <ul class="career-feature-list">
                        <li><span class="career-feature-icon"><i class="fa-solid fa-briefcase"></i></span>Guided resume builder saved to your profile</li>
                        <li><span class="career-feature-icon"><i class="fa-solid fa-users"></i></span>Search and filter live openings</li>
                        <li><span class="career-feature-icon"><i class="fa-solid fa-chart-column"></i></span>Message employers directly about your application</li>
                    </ul>
                    <a href="{{ route('jobs.index') }}" class="career-feature-link">Browse open jobs <span>→</span></a>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="latest-openings">
    <div class="container">
        <div class="d-flex justify-content-between align-items-end mb-4 flex-wrap gap-2">
            <div>
                <h2 class="mb-1">Latest openings</h2>
                <p class="mb-0">Fresh roles posted by verified employers.</p>
            </div>
            <a href="{{ route('jobs.index') }}" class="view-all">View all</a>
        </div>

        <div class="job-list">
            @forelse ($jobs as $job)
                <a href="{{ route('jobs.show', $job) }}" class="job-card d-block p-4 mb-3 text-decoration-none">
                    <div class="d-flex justify-content-between align-items-start mb-3 gap-2">
                        <h3 class="h5 mb-0">{{ $job->title }}</h3>
                        <span class="job-time">{{ $job->published_at?->diffForHumans() ?? $job->created_at->diffForHumans() }}</span>
                    </div>
                    <div class="job-info d-flex flex-wrap gap-3 mb-3">
                        <span><i class="fa-regular fa-building me-1"></i>{{ $job->employer?->company_name ?? 'Employer' }}</span>
                        @if ($job->location)
                            <span><i class="fa-solid fa-location-dot me-1"></i>{{ $job->location }}</span>
                        @endif
                        <span><i class="fa-solid fa-wallet me-1"></i>{{ $job->salaryLabel() }}</span>
                    </div>
                    <div class="job-tags d-flex flex-wrap gap-2">
                        @if ($job->job_type)
                            <span class="tag tag-light">{{ ucwords(str_replace(['-', '_'], ' ', $job->job_type)) }}</span>
                        @endif
                        @if ($job->category)
                            <span class="tag">{{ $job->category->name }}</span>
                        @endif
                        @if ($job->location && str_contains(strtolower($job->location), 'remote'))
                            <span class="tag tag-blue">Remote friendly</span>
                        @endif
                    </div>
                </a>
            @empty
                <div class="job-card d-block p-4 mb-3 text-center text-secondary">
                    No openings published on this site yet. Check back soon.
                </div>
            @endforelse
        </div>
    </div>
</section>

<section class="popular-categories">
    <div class="container">
        <h2 class="mb-4">Popular categories</h2>
        <div class="category-list d-flex flex-wrap gap-2">
            @forelse ($categories as $category)
                <a href="{{ route('jobs.index', ['category_id' => $category->id]) }}" class="category-tag">{{ $category->name }}</a>
            @empty
                @foreach (['Engineering', 'Design', 'Product', 'Marketing', 'Sales', 'Customer Support', 'Finance', 'Healthcare', 'Skilled Trades', 'Education', 'Operations', 'Other'] as $name)
                    <a href="{{ route('jobs.index', ['keyword' => $name]) }}" class="category-tag">{{ $name }}</a>
                @endforeach
            @endforelse
        </div>
    </div>
</section>
@endsection
