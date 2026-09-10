@extends('admin.layouts.app')

@section('title', 'Employers')
@section('heading', 'Employers')
@section('subheading', 'Search, review and manage employer accounts.')

@section('actions')
    <a href="{{ route('admin.employers.create') }}" class="btn btn-brand">
        <i class="bi bi-plus-lg me-1"></i> Add Employer
    </a>
@endsection

@section('content')
    <div class="card mb-3">
        <div class="card-body py-3">
            <form method="GET" action="{{ route('admin.employers.index') }}" class="row g-2 align-items-end">
                <div class="col-md-6">
                    <label for="search" class="form-label">Search</label>
                    <input type="search" name="search" id="search" class="form-control"
                           value="{{ request('search') }}" placeholder="Company, contact, email or phone">
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
                        <a href="{{ route('admin.employers.index') }}" class="btn btn-light" title="Reset">
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
                        <th>Company</th>
                        <th>Contact</th>
                        <th>Email</th>
                        <th>Registered</th>
                        <th class="text-center">Status</th>
                        <th class="text-end" style="width:90px">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($employers as $employer)
                        <tr>
                            <td>
                                <a href="{{ route('admin.employers.show', $employer) }}" class="fw-semibold text-dark">
                                    {{ $employer->company_name }}
                                </a>
                            </td>
                            <td>{{ $employer->name }}</td>
                            <td class="text-secondary">{{ $employer->email }}</td>
                            <td class="text-secondary">{{ $employer->created_at->format('d M Y') }}</td>
                            <td class="text-center">
                                <span class="badge {{ $employer->status->badgeClass() }}">{{ $employer->status->label() }}</span>
                            </td>
                            <td class="text-end">
                                <a href="{{ route('admin.employers.show', $employer) }}"
                                   class="btn btn-sm btn-light" title="View"><i class="bi bi-eye"></i></a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-secondary py-5">
                                <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                                No employers found.
                                <a href="{{ route('admin.employers.create') }}" class="text-brand">Add the first employer</a>.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($employers->hasPages())
            <div class="card-footer bg-white">
                {{ $employers->links() }}
            </div>
        @endif
    </div>
@endsection
