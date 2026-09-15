@extends('layouts.marketing')

@section('title', 'My applications')

@section('content')
<section class="candidate-panel my-applications-section">
    <div class="container">
        <div class="row">
            <div class="col-10 mx-auto">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
                    <div>
                        <h1 class="panel-title mb-0">My applications</h1>
                    </div>
                    <form method="GET" action="{{ route('candidate.applications.index') }}" class="d-flex align-items-center gap-2">
                        <label for="status" class="visually-hidden">Status</label>
                        <select name="status" id="status" class="form-select form-select-sm" style="min-width: 180px" onchange="this.form.submit()">
                            <option value="">All statuses</option>
                            @foreach ($statuses as $value => $label)
                                <option value="{{ $value }}" @selected($currentStatus === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </form>
                </div>

                @forelse ($applications as $application)
                    @php($job = $application->jobPosting)
                    <a href="{{ route('candidate.applications.show', $application) }}" class="application-card d-flex align-items-center justify-content-between text-decoration-none mb-3">
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
                    <div class="panel-card">
                        <div class="panel-card-body text-center">
                            <p class="panel-subtitle mb-3">No applications yet.</p>
                            <a href="{{ route('jobs.index') }}" class="btn btn-primary">Browse jobs</a>
                        </div>
                    </div>
                @endforelse

                @if ($applications->hasPages())
                    <div class="mt-4">{{ $applications->links() }}</div>
                @endif
            </div>
        </div>
    </div>
</section>
@endsection
