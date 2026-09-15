@extends('layouts.marketing')

@section('title', 'Find Jobs')

@section('content')
<section class="candidate-panel">
    <div class="container">
        <div class="mb-4">
            <h1 class="panel-title">Find jobs</h1>
            <p class="panel-subtitle">Browse openings on {{ setting('site_name', config('app.name')) }}.</p>
        </div>

        <div class="panel-card mb-4">
            <div class="panel-card-body">
                <form method="GET" action="{{ route('candidate.jobs.index') }}" class="row g-2 align-items-end">
                    <div class="col-md-3">
                        <label for="keyword" class="form-label">Keyword</label>
                        <input type="text" name="keyword" id="keyword" class="form-control"
                               value="{{ $filters['keyword'] ?? '' }}" placeholder="Title, company, skill">
                    </div>
                    <div class="col-md-2">
                        <label for="location" class="form-label">Location</label>
                        <input type="text" name="location" id="location" class="form-control"
                               value="{{ $filters['location'] ?? '' }}">
                    </div>
                    <div class="col-md-2">
                        <label for="category_id" class="form-label">Category</label>
                        <select name="category_id" id="category_id" class="form-select">
                            <option value="">All</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}" @selected((string) ($filters['category_id'] ?? '') === (string) $category->id)>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="job_type" class="form-label">Job type</label>
                        <select name="job_type" id="job_type" class="form-select">
                            <option value="">All</option>
                            @foreach ($jobTypes as $value => $label)
                                <option value="{{ $value }}" @selected(($filters['job_type'] ?? '') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="experience_level" class="form-label">Experience</label>
                        <select name="experience_level" id="experience_level" class="form-select">
                            <option value="">All</option>
                            @foreach ($experienceLevels as $value => $label)
                                <option value="{{ $value }}" @selected(($filters['experience_level'] ?? '') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="salary_min" class="form-label">Salary min</label>
                        <input type="number" min="0" step="1" name="salary_min" id="salary_min" class="form-control"
                               value="{{ $filters['salary_min'] ?? '' }}">
                    </div>
                    <div class="col-md-2">
                        <label for="salary_max" class="form-label">Salary max</label>
                        <input type="number" min="0" step="1" name="salary_max" id="salary_max" class="form-control"
                               value="{{ $filters['salary_max'] ?? '' }}">
                    </div>
                    <div class="col-md-2">
                        <label for="posted_within" class="form-label">Date posted</label>
                        <select name="posted_within" id="posted_within" class="form-select">
                            <option value="">Any time</option>
                            @foreach ($postedWithinOptions as $value => $label)
                                <option value="{{ $value }}" @selected(($filters['posted_within'] ?? '') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">Search</button>
                    </div>
                    <div class="col-md-2">
                        <a href="{{ route('candidate.jobs.index') }}" class="btn btn-outline-secondary w-100">Reset</a>
                    </div>
                </form>
            </div>
        </div>

        <div class="row g-3">
            @forelse ($jobs as $job)
                <div class="col-md-6">
                    <div class="panel-card h-100">
                        <div class="panel-card-body d-flex flex-column">
                            <div class="d-flex justify-content-between gap-2 mb-2">
                                <div>
                                    <h2 class="h5 mb-1">
                                        <a href="{{ route('candidate.jobs.show', $job) }}" class="text-decoration-none text-dark">
                                            {{ $job->title }}
                                        </a>
                                    </h2>
                                    <div class="text-secondary small">
                                        {{ $job->employer?->company_name ?? 'Employer' }}
                                        @if ($job->location) · {{ $job->location }} @endif
                                    </div>
                                </div>
                                @if (in_array($job->id, $appliedJobIds, true))
                                    <span class="badge text-bg-primary align-self-start">Applied</span>
                                @endif
                            </div>

                            <p class="small text-secondary flex-grow-1">
                                {{ \Illuminate\Support\Str::limit(strip_tags($job->description), 140) }}
                            </p>

                            <div class="d-flex flex-wrap gap-2 small text-secondary mb-3">
                                @if ($job->category)
                                    <span class="badge bg-light text-dark border">{{ $job->category->name }}</span>
                                @endif
                                @if ($job->job_type)
                                    <span class="badge bg-light text-dark border">{{ ucwords(str_replace(['-', '_'], ' ', $job->job_type)) }}</span>
                                @endif
                                <span>{{ $job->salaryLabel() }}</span>
                            </div>

                            <div class="d-flex gap-2 flex-wrap">
                                <a href="{{ route('candidate.jobs.show', $job) }}" class="btn btn-sm btn-primary">View</a>
                                @if (in_array($job->id, $savedJobIds, true))
                                    <form method="POST" action="{{ route('candidate.jobs.unsave', $job) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-secondary">Unsave</button>
                                    </form>
                                @else
                                    <form method="POST" action="{{ route('candidate.jobs.save', $job) }}">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-secondary">Save</button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="panel-card">
                        <div class="panel-card-body text-center text-secondary py-5">
                            No jobs match your filters right now.
                        </div>
                    </div>
                </div>
            @endforelse
        </div>

        @if ($jobs->hasPages())
            <div class="mt-4">{{ $jobs->links() }}</div>
        @endif
    </div>
</section>
@endsection
