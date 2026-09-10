<div class="row g-3">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">Account</div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="name" class="form-label required">Name</label>
                        <input type="text" name="name" id="name"
                               class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name', $moderator->name) }}" required>
                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label for="email" class="form-label required">Email</label>
                        <input type="email" name="email" id="email"
                               class="form-control @error('email') is-invalid @enderror"
                               value="{{ old('email', $moderator->email) }}" required>
                        @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label for="phone" class="form-label">Phone</label>
                        <input type="text" name="phone" id="phone"
                               class="form-control @error('phone') is-invalid @enderror"
                               value="{{ old('phone', $moderator->phone) }}">
                        @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label for="admin_role_id" class="form-label required">Role</label>
                        <select name="admin_role_id" id="admin_role_id"
                                class="form-select @error('admin_role_id') is-invalid @enderror" required>
                            <option value="">Select role</option>
                            @foreach ($roles as $role)
                                <option value="{{ $role->id }}"
                                    @selected((string) old('admin_role_id', $moderator->admin_role_id) === (string) $role->id)>
                                    {{ $role->name }}{{ $role->is_active ? '' : ' (inactive)' }}
                                </option>
                            @endforeach
                        </select>
                        @error('admin_role_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label for="password" class="form-label {{ $moderator->exists ? '' : 'required' }}">Password</label>
                        <input type="password" name="password" id="password"
                               class="form-control @error('password') is-invalid @enderror"
                               {{ $moderator->exists ? '' : 'required' }}>
                        @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        @if ($moderator->exists)
                            <div class="form-text">Leave blank to keep the current password.</div>
                        @endif
                    </div>
                    <div class="col-md-6">
                        <label for="password_confirmation" class="form-label">Confirm password</label>
                        <input type="password" name="password_confirmation" id="password_confirmation" class="form-control">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card mb-3">
            <div class="card-header">Status</div>
            <div class="card-body">
                <input type="hidden" name="is_active" value="0">
                <div class="form-check form-switch">
                    <input type="checkbox" name="is_active" id="is_active" value="1" class="form-check-input"
                           @checked(old('is_active', $moderator->is_active ?? true))>
                    <label for="is_active" class="form-check-label">Active</label>
                </div>
                <div class="form-text">Inactive moderators cannot sign in.</div>
            </div>
        </div>

        <div class="card">
            <div class="card-body d-flex flex-column gap-2">
                <button type="submit" class="btn btn-brand">
                    {{ $moderator->exists ? 'Update moderator' : 'Create moderator' }}
                </button>
                <a href="{{ route('admin.moderators.index') }}" class="btn btn-light">Cancel</a>
            </div>
        </div>
    </div>
</div>
