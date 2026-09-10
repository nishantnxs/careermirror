<div class="row g-3">
    <div class="col-lg-8">
        <div class="card mb-3">
            <div class="card-header">Employer details</div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="company_name" class="form-label required">Company name</label>
                        <input type="text" name="company_name" id="company_name"
                               class="form-control @error('company_name') is-invalid @enderror"
                               value="{{ old('company_name', $employer->company_name) }}" required
                               autocomplete="organization">
                        @error('company_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="name" class="form-label required">Contact name</label>
                        <input type="text" name="name" id="name"
                               class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name', $employer->name) }}" required
                               autocomplete="name">
                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="email" class="form-label required">Email address</label>
                        <input type="email" name="email" id="email"
                               class="form-control @error('email') is-invalid @enderror"
                               value="{{ old('email', $employer->email) }}" required
                               autocomplete="email">
                        @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="phone" class="form-label">Phone</label>
                        <input type="text" name="phone" id="phone"
                               class="form-control @error('phone') is-invalid @enderror"
                               value="{{ old('phone', $employer->phone) }}"
                               autocomplete="tel">
                        @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">Login credentials</div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="password" class="form-label required">Password</label>
                        <input type="password" name="password" id="password"
                               class="form-control @error('password') is-invalid @enderror"
                               required autocomplete="new-password">
                        @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="password_confirmation" class="form-label required">Confirm password</label>
                        <input type="password" name="password_confirmation" id="password_confirmation"
                               class="form-control" required autocomplete="new-password">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card mb-3">
            <div class="card-header">Account status</div>
            <div class="card-body">
                <label for="status" class="form-label required">Status</label>
                <select name="status" id="status"
                        class="form-select @error('status') is-invalid @enderror" required>
                    @foreach ($statuses as $value => $label)
                        <option value="{{ $value }}"
                            @selected(old('status', $employer->status?->value ?? 'active') === $value)>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
                @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                <div class="form-text">
                    Active accounts can sign in immediately. Inactive or suspended accounts cannot.
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header">Allowed domains</div>
            <div class="card-body">
                <p class="text-secondary small">
                    Domains this employer can select when posting jobs.
                </p>
                @php($selectedDomainIds = collect($selectedDomainIds ?? [])->map(fn ($id) => (int) $id)->all())
                @forelse ($domains ?? [] as $domain)
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" name="domain_ids[]"
                               id="create_domain_{{ $domain->id }}" value="{{ $domain->id }}"
                               @checked(in_array($domain->id, $selectedDomainIds, true))>
                        <label class="form-check-label" for="create_domain_{{ $domain->id }}">
                            {{ $domain->host }}
                        </label>
                    </div>
                @empty
                    <p class="text-danger small mb-0">No active domains. Create a domain first.</p>
                @endforelse
                @error('domain_ids') <div class="text-danger small mt-2">{{ $message }}</div> @enderror
            </div>
        </div>

        <div class="d-grid gap-2">
            <button type="submit" class="btn btn-brand">
                <i class="bi bi-check-lg me-1"></i> Create employer
            </button>
            <a href="{{ route('admin.employers.index') }}" class="btn btn-light">Cancel</a>
        </div>
    </div>
</div>
