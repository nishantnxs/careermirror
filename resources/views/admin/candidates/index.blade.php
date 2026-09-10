@extends('admin.layouts.app')

@section('title', 'Candidates')
@section('heading', 'Candidates')
@section('subheading', 'Search, review and manage candidate accounts.')

@section('actions')
    <a href="{{ route('admin.candidates.create') }}" class="btn btn-brand">
        <i class="bi bi-plus-lg me-1"></i> Add Candidate
    </a>
@endsection

@section('content')
    <div class="card mb-3">
        <div class="card-body py-3">
            <form method="GET" action="{{ route('admin.candidates.index') }}" class="row g-2 align-items-end">
                <div class="col-md-6">
                    <label for="search" class="form-label">Search</label>
                    <input type="search" name="search" id="search" class="form-control"
                           value="{{ request('search') }}" placeholder="Name, email or phone">
                </div>
                <div class="col-md-3">
                    <label for="status" class="form-label">Status</label>
                    <select name="status" id="status" class="form-select">
                        <option value="">All statuses</option>
                        @foreach ($statuses as $value => $label)
                            <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-brand w-100">
                        <i class="bi bi-funnel"></i>
                    </button>
                    @if (request()->hasAny(['search', 'status']))
                        <a href="{{ route('admin.candidates.index') }}" class="btn btn-light" title="Reset">
                            <i class="bi bi-x-lg"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Registered</th>
                        <th class="text-center">Status</th>
                        <th class="text-end" style="width:90px">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($candidates as $candidate)
                        <tr>
                            <td>
                                <a href="{{ route('admin.candidates.show', $candidate) }}" class="fw-semibold text-dark">
                                    {{ $candidate->name }}
                                </a>
                            </td>
                            <td class="text-secondary">{{ $candidate->email }}</td>
                            <td class="text-secondary">{{ $candidate->phone ?: '—' }}</td>
                            <td class="text-secondary">{{ $candidate->created_at->format('d M Y') }}</td>
                            <td class="text-center">
                                <span class="badge {{ $candidate->status->badgeClass() }}">{{ $candidate->status->label() }}</span>
                            </td>
                            <td class="text-end">
                                <a href="{{ route('admin.candidates.show', $candidate) }}"
                                   class="btn btn-sm btn-light" title="View"><i class="bi bi-eye"></i></a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-secondary py-5">
                                <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                                No candidates found.
                                <a href="{{ route('admin.candidates.create') }}" class="text-brand">Add the first candidate</a>.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($candidates->hasPages())
            <div class="card-footer bg-white">
                {{ $candidates->links() }}
            </div>
        @endif
    </div>
@endsection
