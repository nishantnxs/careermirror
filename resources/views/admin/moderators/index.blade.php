@extends('admin.layouts.app')

@section('title', 'Moderators')
@section('heading', 'Moderators')
@section('subheading', 'Create moderators and control access through roles.')

@section('actions')
    <a href="{{ route('admin.moderators.create') }}" class="btn btn-brand">
        <i class="bi bi-plus-lg me-1"></i> Add moderator
    </a>
@endsection

@section('content')
    <div class="card mb-3">
        <div class="card-body py-3">
            <form method="GET" action="{{ route('admin.moderators.index') }}" class="row g-2 align-items-end">
                <div class="col-md-6">
                    <label for="search" class="form-label">Search</label>
                    <input type="search" name="search" id="search" class="form-control"
                           value="{{ request('search') }}" placeholder="Name, email or phone">
                </div>
                <div class="col-md-3">
                    <label for="status" class="form-label">Status</label>
                    <select name="status" id="status" class="form-select">
                        <option value="">All</option>
                        <option value="active" @selected(request('status') === 'active')>Active</option>
                        <option value="inactive" @selected(request('status') === 'inactive')>Inactive</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-brand w-100"><i class="bi bi-funnel"></i></button>
                    @if (request()->hasAny(['search', 'status']))
                        <a href="{{ route('admin.moderators.index') }}" class="btn btn-light"><i class="bi bi-x-lg"></i></a>
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
                        <th>Moderator</th>
                        <th>Role</th>
                        <th class="text-center">Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($moderators as $moderator)
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $moderator->name }}</div>
                                <div class="text-secondary small">{{ $moderator->email }}</div>
                            </td>
                            <td>{{ $moderator->role?->name ?? '—' }}</td>
                            <td class="text-center">
                                @if ($moderator->is_active)
                                    <span class="badge bg-success-subtle text-success-emphasis">Active</span>
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary-emphasis">Inactive</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <a href="{{ route('admin.moderators.edit', $moderator) }}" class="btn btn-sm btn-light">Edit</a>
                                <form method="POST" action="{{ route('admin.moderators.toggle-status', $moderator) }}" class="d-inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-sm btn-outline-secondary">
                                        {{ $moderator->is_active ? 'Deactivate' : 'Activate' }}
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-secondary py-4">No moderators yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($moderators->hasPages())
            <div class="card-footer">{{ $moderators->links() }}</div>
        @endif
    </div>
@endsection
