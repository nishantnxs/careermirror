@php
    use App\Enums\AdminPermissionModule;
    $selected = collect(old('permissions', $selectedPermissionIds))->map(fn ($id) => (int) $id)->all();
@endphp

<div class="row g-3">
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">Role details</div>
            <div class="card-body">
                <div class="mb-3">
                    <label for="name" class="form-label required">Name</label>
                    <input type="text" name="name" id="name"
                           class="form-control @error('name') is-invalid @enderror"
                           value="{{ old('name', $role->name) }}" required
                           placeholder="e.g. Job Moderator">
                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label for="slug" class="form-label">Slug</label>
                    <input type="text" name="slug" id="slug"
                           class="form-control @error('slug') is-invalid @enderror"
                           value="{{ old('slug', $role->slug) }}"
                           placeholder="Leave blank to auto-generate">
                    @error('slug') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <textarea name="description" id="description" rows="3"
                              class="form-control @error('description') is-invalid @enderror">{{ old('description', $role->description) }}</textarea>
                    @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <input type="hidden" name="is_active" value="0">
                <div class="form-check form-switch">
                    <input type="checkbox" name="is_active" id="is_active" value="1" class="form-check-input"
                           @checked(old('is_active', $role->is_active ?? true))>
                    <label for="is_active" class="form-check-label">Active</label>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">Module permissions</div>
            <div class="card-body">
                @forelse ($permissionsByModule as $module => $permissions)
                    @php
                        $moduleEnum = AdminPermissionModule::tryFrom($module);
                        $moduleLabel = $moduleEnum?->label() ?? Str::headline($module);
                    @endphp
                    <div class="mb-4">
                        <div class="fw-semibold mb-2">{{ $moduleLabel }}</div>
                        <div class="row g-2">
                            @foreach ($permissions as $permission)
                                <div class="col-md-6">
                                    <label class="border rounded p-3 w-100 h-100">
                                        <input type="checkbox" class="form-check-input me-2"
                                               name="permissions[]" value="{{ $permission->id }}"
                                               @checked(in_array($permission->id, $selected, true))>
                                        <span class="fw-semibold">{{ $permission->name }}</span>
                                        @if ($permission->description)
                                            <div class="text-secondary small mt-1">{{ $permission->description }}</div>
                                        @endif
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @empty
                    <p class="text-secondary mb-0">No permissions seeded yet. Run the AdminAccessSeeder.</p>
                @endforelse
                @error('permissions') <div class="text-danger small">{{ $message }}</div> @enderror
            </div>
        </div>

        <div class="d-flex gap-2 mt-3">
            <button type="submit" class="btn btn-brand">
                {{ $role->exists ? 'Update role' : 'Create role' }}
            </button>
            <a href="{{ route('admin.roles.index') }}" class="btn btn-light">Cancel</a>
        </div>
    </div>
</div>
