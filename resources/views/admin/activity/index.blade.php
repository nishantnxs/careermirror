@extends('admin.layouts.app')

@section('title', 'Activity Log')
@section('heading', 'Activity Log')
@section('subheading', 'Audit trail of important system changes.')

@section('content')
    <div class="card mb-3">
        <div class="card-body py-3">
            <form method="GET" action="{{ route('admin.activity.index') }}" class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label for="search" class="form-label">Search</label>
                    <input type="search" name="search" id="search" class="form-control"
                           value="{{ request('search') }}" placeholder="Actor, record or description">
                </div>
                <div class="col-md-2">
                    <label for="action" class="form-label">Action</label>
                    <select name="action" id="action" class="form-select">
                        <option value="">All actions</option>
                        @foreach ($actions as $value => $label)
                            <option value="{{ $value }}" @selected(request('action') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="actor_guard" class="form-label">Actor type</label>
                    <select name="actor_guard" id="actor_guard" class="form-select">
                        <option value="">All</option>
                        @foreach ($guards as $value => $label)
                            <option value="{{ $value }}" @selected(request('actor_guard') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="from" class="form-label">From</label>
                    <input type="date" name="from" id="from" class="form-control" value="{{ request('from') }}">
                </div>
                <div class="col-md-2">
                    <label for="to" class="form-label">To</label>
                    <input type="date" name="to" id="to" class="form-control" value="{{ request('to') }}">
                </div>
                <div class="col-md-1 d-flex gap-2">
                    <button type="submit" class="btn btn-brand w-100"><i class="bi bi-funnel"></i></button>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>When</th>
                        <th>Actor</th>
                        <th>Action</th>
                        <th>Record</th>
                        <th class="text-end">Details</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($logs as $log)
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $log->created_at->format('d M Y') }}</div>
                                <div class="text-secondary small">{{ $log->created_at->format('h:i A') }}</div>
                            </td>
                            <td>
                                <div>{{ $log->actor_name ?: 'System' }}</div>
                                <div class="text-secondary small text-capitalize">{{ $log->actor_guard ?: 'system' }}</div>
                            </td>
                            <td>
                                <span class="badge bg-primary-subtle text-primary-emphasis">{{ $log->action->label() }}</span>
                                <div class="text-secondary small mt-1">{{ $log->description }}</div>
                            </td>
                            <td>{{ $log->subject_label ?: '—' }}</td>
                            <td class="text-end">
                                <a href="{{ route('admin.activity.show', $log) }}" class="btn btn-sm btn-light">View</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-secondary py-4">No activity recorded yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($logs->hasPages())
            <div class="card-footer">{{ $logs->links() }}</div>
        @endif
    </div>
@endsection
