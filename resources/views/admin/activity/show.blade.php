@extends('admin.layouts.app')

@section('title', 'Activity Detail')
@section('heading', $log->action->label())
@section('subheading', $log->created_at->format('d M Y, h:i A'))

@section('actions')
    <a href="{{ route('admin.activity.index') }}" class="btn btn-light">
        <i class="bi bi-arrow-left me-1"></i> Back to log
    </a>
@endsection

@section('content')
    <div class="row g-3">
        <div class="col-lg-5">
            <div class="card">
                <div class="card-header">Event</div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-4 text-secondary fw-normal">Actor</dt>
                        <dd class="col-sm-8">
                            {{ $log->actor_name ?: 'System' }}
                            <div class="text-secondary small text-capitalize">{{ $log->actor_guard ?: 'system' }}</div>
                        </dd>

                        <dt class="col-sm-4 text-secondary fw-normal">Action</dt>
                        <dd class="col-sm-8">{{ $log->action->label() }}</dd>

                        <dt class="col-sm-4 text-secondary fw-normal">Description</dt>
                        <dd class="col-sm-8">{{ $log->description }}</dd>

                        <dt class="col-sm-4 text-secondary fw-normal">Record</dt>
                        <dd class="col-sm-8">{{ $log->subject_label ?: '—' }}</dd>

                        <dt class="col-sm-4 text-secondary fw-normal">Date</dt>
                        <dd class="col-sm-8">{{ $log->created_at->format('d M Y') }}</dd>

                        <dt class="col-sm-4 text-secondary fw-normal">Time</dt>
                        <dd class="col-sm-8">{{ $log->created_at->format('h:i:s A') }}</dd>

                        <dt class="col-sm-4 text-secondary fw-normal">IP</dt>
                        <dd class="col-sm-8 mb-0">{{ $log->ip_address ?: '—' }}</dd>
                    </dl>
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="card mb-3">
                <div class="card-header">Previous values</div>
                <div class="card-body">
                    @if (filled($log->old_values))
                        <pre class="mb-0 small bg-light border rounded p-3">{{ json_encode($log->old_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                    @else
                        <p class="text-secondary small mb-0">No previous values recorded.</p>
                    @endif
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header">New values</div>
                <div class="card-body">
                    @if (filled($log->new_values))
                        <pre class="mb-0 small bg-light border rounded p-3">{{ json_encode($log->new_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                    @else
                        <p class="text-secondary small mb-0">No new values recorded.</p>
                    @endif
                </div>
            </div>

            @if (filled($log->properties))
                <div class="card">
                    <div class="card-header">Extra details</div>
                    <div class="card-body">
                        <pre class="mb-0 small bg-light border rounded p-3">{{ json_encode($log->properties, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection
