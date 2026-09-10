@extends('admin.layouts.app')

@section('title', 'Domains')
@section('heading', 'Domains / Websites')
@section('subheading', 'Manage multiple websites from this central admin panel.')

@section('actions')
    @admincan('domains.manage')
        <a href="{{ route('admin.domains.create') }}" class="btn btn-brand">
            <i class="bi bi-plus-lg me-1"></i> Add Domain
        </a>
    @endadmincan
@endsection

@section('content')
    <div class="card mb-3">
        <div class="card-body py-3">
            <form method="GET" action="{{ route('admin.domains.index') }}" class="row g-2 align-items-end">
                <div class="col-md-6">
                    <label for="search" class="form-label">Search</label>
                    <input type="search" name="search" id="search" class="form-control"
                           value="{{ request('search') }}" placeholder="Host, name or URL">
                </div>
                <div class="col-md-3">
                    <label for="status" class="form-label">Status</label>
                    <select name="status" id="status" class="form-select">
                        <option value="">All</option>
                        @foreach ($statuses as $value => $label)
                            <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-2">
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
                        <th>Domain</th>
                        <th>Website</th>
                        <th>Status</th>
                        <th>Default</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($domains as $domain)
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $domain->host }}</div>
                                <div class="text-secondary small">{{ $domain->url }}</div>
                            </td>
                            <td>{{ $domain->displayName() }}</td>
                            <td><span class="badge {{ $domain->status->badgeClass() }}">{{ $domain->status->label() }}</span></td>
                            <td>
                                @if ($domain->is_default)
                                    <span class="badge bg-primary-subtle text-primary-emphasis">Default</span>
                                @else
                                    —
                                @endif
                            </td>
                            <td class="text-end">
                                @admincan('domains.manage')
                                    <a href="{{ route('admin.domains.settings.edit', $domain) }}" class="btn btn-sm btn-brand">Settings</a>
                                    <a href="{{ route('admin.domains.edit', $domain) }}" class="btn btn-sm btn-light">Edit</a>
                                    <form action="{{ route('admin.domains.toggle-status', $domain) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button class="btn btn-sm btn-outline-secondary">
                                            {{ $domain->isActive() ? 'Deactivate' : 'Activate' }}
                                        </button>
                                    </form>
                                    @unless ($domain->is_default)
                                        <form action="{{ route('admin.domains.destroy', $domain) }}" method="POST" class="d-inline"
                                              onsubmit="return confirm('Delete this domain? Related settings will be removed.');">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger">Delete</button>
                                        </form>
                                    @endunless
                                @endadmincan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-secondary py-4">No domains configured yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($domains->hasPages())
            <div class="card-footer">{{ $domains->links() }}</div>
        @endif
    </div>
@endsection
