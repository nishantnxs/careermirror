@extends('layouts.portal')

@section('title', 'Jobs')
@section('account_label', auth('employer')->user()->company_name)
@section('logout_action', route('employer.logout'))

@section('nav')
    @include('employer.partials.nav', ['active' => 'jobs'])
@endsection

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-4">
        <div>
            <h1 class="h4 fw-semibold mb-1">Job postings</h1>
            <p class="text-secondary mb-0">
                @if ($subscription)
                    {{ $subscription->remainingJobs() }} of {{ $subscription->jobs_allowed }} job credit(s) remaining
                    @if ($subscription->ends_at)
                        &middot; plan expires {{ $subscription->ends_at->format('d M Y') }}
                    @endif
                @else
                    Buy a plan to start posting jobs.
                @endif
            </p>
        </div>
        <a href="{{ route('employer.jobs.create') }}" class="btn btn-brand">
            <i class="bi bi-plus-lg me-1"></i> Post job
        </a>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Location</th>
                        <th>Status</th>
                        <th>Expires</th>
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
                            <td class="text-end">
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
                            <td colspan="5" class="text-center text-secondary py-4">No jobs posted yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($jobs->hasPages())
            <div class="card-footer">{{ $jobs->links() }}</div>
        @endif
    </div>
@endsection
