@extends('layouts.marketing')

@section('title', 'Saved Jobs')

@section('content')
<section class="candidate-panel">
    <div class="container">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
            <div>
                <h1 class="panel-title">Saved jobs</h1>
                <p class="panel-subtitle">Jobs you bookmarked to review later.</p>
            </div>
            <a href="{{ route('jobs.index') }}" class="btn btn-primary">Find jobs</a>
        </div>

        <div class="row g-3">
            @forelse ($savedJobs as $saved)
                @php($job = $saved->jobPosting)
                <div class="col-md-6">
                    <div class="panel-card h-100">
                        <div class="panel-card-body d-flex flex-column">
                            @if ($job)
                                <h2 class="h5 mb-1">
                                    <a href="{{ route('jobs.index', ['job' => $job->id]) }}" class="text-decoration-none text-dark">
                                        {{ $job->title }}
                                    </a>
                                </h2>
                                <div class="text-secondary small mb-2">
                                    {{ $job->employer?->company_name }}
                                    @if ($job->location) · {{ $job->location }} @endif
                                </div>
                                <p class="small text-secondary flex-grow-1">
                                    {{ \Illuminate\Support\Str::limit(strip_tags($job->description), 120) }}
                                </p>
                                <div class="d-flex gap-2">
                                    <a href="{{ route('jobs.index', ['job' => $job->id]) }}" class="btn btn-sm btn-primary">View</a>
                                    <form method="POST" action="{{ route('candidate.jobs.unsave', $job) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-secondary">Remove</button>
                                    </form>
                                </div>
                            @else
                                <p class="text-secondary mb-0">This job is no longer available.</p>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="panel-card">
                        <div class="panel-card-body text-center text-secondary py-5">
                            No saved jobs yet.
                        </div>
                    </div>
                </div>
            @endforelse
        </div>

        @if ($savedJobs->hasPages())
            <div class="mt-4">{{ $savedJobs->links() }}</div>
        @endif
    </div>
</section>
@endsection
