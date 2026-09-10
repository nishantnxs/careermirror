@extends('admin.layouts.app')

@section('title', 'Roles')
@section('heading', 'Roles')
@section('subheading', 'Create roles and assign module permissions for moderators.')

@section('actions')
    <a href="{{ route('admin.roles.create') }}" class="btn btn-brand">
        <i class="bi bi-plus-lg me-1"></i> Add role
    </a>
@endsection

@section('content')
    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Role</th>
                        <th>Permissions</th>
                        <th>Moderators</th>
                        <th class="text-center">Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($roles as $role)
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $role->name }}</div>
                                <div class="text-secondary small">{{ $role->description ?: $role->slug }}</div>
                            </td>
                            <td>{{ $role->permissions_count }}</td>
                            <td>{{ $role->admins_count }}</td>
                            <td class="text-center">
                                @if ($role->is_active)
                                    <span class="badge bg-success-subtle text-success-emphasis">Active</span>
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary-emphasis">Inactive</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <a href="{{ route('admin.roles.edit', $role) }}" class="btn btn-sm btn-light">Edit</a>
                                <form method="POST" action="{{ route('admin.roles.destroy', $role) }}" class="d-inline"
                                      onsubmit="return confirm('Delete this role?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-secondary py-4">No roles yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($roles->hasPages())
            <div class="card-footer">{{ $roles->links() }}</div>
        @endif
    </div>
@endsection
