@extends('layouts.marketing')

@section('title', 'My jobs')

@section('content')
<section class="employer-panel">
    <div class="container">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
            <div>
                <h1 class="panel-title">My jobs</h1>
                <p class="panel-subtitle">
                    @if ($subscription)
                        {{ $subscription->remainingJobs() }} of {{ $subscription->jobs_allowed }} job credit(s) remaining
                        @if ($subscription->ends_at)
                            · plan expires {{ $subscription->ends_at->format('d M Y') }}
                        @endif
                    @else
                        Buy a plan to start posting jobs.
                    @endif
                </p>
            </div>
            <a href="{{ route('employer.jobs.create') }}" class="btn btn-primary">Post a job</a>
        </div>

        <div class="panel-card">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Location</th>
                            <th>Status</th>
                            <th>Expires</th>
                            <th>Applicants</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($jobs as $job)
                            <tr>
                                <td class="fw-semibold">{{ $job->title }}</td>
                                <td class="text-secondary">{{ $job->location ?: '—' }}</td>
                                <td>
                                    <span class="badge {{ $job->status->badgeClass() }}">{{ $job->status->label() }}</span>
                                </td>
                                <td class="text-secondary">{{ $job->expires_at?->format('d M Y') ?? '—' }}</td>
                                <td>
                                    <a href="{{ route('employer.applicants.index', ['job' => $job->id]) }}">
                                        {{ $job->applications_count }}
                                    </a>
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('employer.applicants.index', ['job' => $job->id]) }}" class="btn btn-sm btn-primary">Applicants</a>
                                    <a href="{{ route('employer.jobs.edit', $job) }}" class="btn btn-sm btn-light">Edit</a>
                                    <form method="POST" action="{{ route('employer.jobs.destroy', $job) }}" class="d-inline"
                                          onsubmit="return confirm('Delete this job posting?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-secondary py-4">No jobs posted yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($jobs->hasPages())
                <div class="panel-card-footer">{{ $jobs->links() }}</div>
            @endif
        </div>
    </div>
</section>
@endsection
