@extends('admin.layouts.app')

@section('title', 'Categories')
@section('heading', 'Categories')
@section('subheading', 'Manage job categories shown to employers when posting jobs.')

@section('actions')
    @admincan('categories.manage')
        <a href="{{ route('admin.categories.create') }}" class="btn btn-brand">
            <i class="bi bi-plus-lg me-1"></i> Add Category
        </a>
    @endadmincan
@endsection

@section('content')
    <div class="card mb-3">
        <div class="card-body py-3">
            <form method="GET" action="{{ route('admin.categories.index') }}" class="row g-2 align-items-end">
                <div class="col-md-6">
                    <label for="search" class="form-label">Search</label>
                    <input type="search" name="search" id="search" class="form-control"
                           value="{{ request('search') }}" placeholder="Name or slug">
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
                        <a href="{{ route('admin.categories.index') }}" class="btn btn-light" title="Reset">
                            <i class="bi bi-x-lg"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">Approved categories</div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Slug</th>
                        <th>Order</th>
                        <th>Active</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($managed as $category)
                        <tr>
                            <td class="fw-semibold">{{ $category->name }}</td>
                            <td><code>{{ $category->slug }}</code></td>
                            <td>{{ $category->sort_order }}</td>
                            <td>
                                @if ($category->is_active)
                                    <span class="badge bg-success-subtle text-success-emphasis">Active</span>
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary-emphasis">Inactive</span>
                                @endif
                            </td>
                            <td class="text-end">
                                @admincan('categories.manage')
                                    <a href="{{ route('admin.categories.edit', $category) }}" class="btn btn-sm btn-light">Edit</a>
                                    <form action="{{ route('admin.categories.destroy', $category) }}" method="POST" class="d-inline"
                                          onsubmit="return confirm('Delete this category?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                    </form>
                                @endadmincan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-secondary py-4">No categories yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($managed->hasPages())
            <div class="card-footer">{{ $managed->links() }}</div>
        @endif
    </div>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span>Employer-added categories</span>
            <span class="badge bg-warning-subtle text-warning-emphasis">{{ $suggestions->total() }} pending</span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Suggested by</th>
                        <th>Suggestions</th>
                        <th>Submitted</th>
                        <th class="text-end">Review</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($suggestions as $suggestion)
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $suggestion->name }}</div>
                                <div class="text-secondary small"><code>{{ $suggestion->slug }}</code></div>
                            </td>
                            <td>
                                {{ $suggestion->suggestedBy?->company_name ?? '—' }}
                                @if ($suggestion->suggestedBy?->email)
                                    <div class="text-secondary small">{{ $suggestion->suggestedBy->email }}</div>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-primary-subtle text-primary-emphasis">
                                    {{ $suggestion->suggestion_count }}
                                </span>
                            </td>
                            <td>
                                <div>{{ $suggestion->created_at->format('d M Y') }}</div>
                                <div class="text-secondary small">{{ $suggestion->created_at->format('h:i A') }}</div>
                            </td>
                            <td class="text-end">
                                @admincan('categories.review')
                                    <form action="{{ route('admin.categories.approve', $suggestion) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success">Approve</button>
                                    </form>
                                    <form action="{{ route('admin.categories.reject', $suggestion) }}" method="POST" class="d-inline"
                                          onsubmit="return confirm('Reject this category suggestion?');">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-danger">Reject</button>
                                    </form>
                                @endadmincan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-secondary py-4">No employer suggestions waiting for review.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($suggestions->hasPages())
            <div class="card-footer">{{ $suggestions->links() }}</div>
        @endif
    </div>
@endsection
