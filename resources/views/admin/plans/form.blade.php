@php
    $features = old('features', $plan->features ?? []);
    $features = empty($features) ? [''] : $features;
@endphp

<div class="row g-3">
    <div class="col-lg-8">
        <div class="card mb-3">
            <div class="card-header">Plan details</div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-7">
                        <label for="title" class="form-label required">Title</label>
                        <input type="text" name="title" id="title"
                               class="form-control @error('title') is-invalid @enderror"
                               value="{{ old('title', $plan->title) }}" required
                               placeholder="e.g. Premium Yearly">
                        @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-5">
                        <label for="plan_type" class="form-label required">Plan type</label>
                        <select name="plan_type" id="plan_type"
                                class="form-select @error('plan_type') is-invalid @enderror" required>
                            @foreach ($planTypes as $value => $label)
                                <option value="{{ $value }}"
                                    @selected(old('plan_type', $plan->plan_type?->value) === $value)>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                        @error('plan_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-12">
                        <label for="slug" class="form-label">Slug</label>
                        <input type="text" name="slug" id="slug"
                               class="form-control @error('slug') is-invalid @enderror"
                               value="{{ old('slug', $plan->slug) }}"
                               placeholder="Leave blank to generate from the title">
                        @error('slug') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-12">
                        <label for="description" class="form-label">Description</label>
                        <textarea name="description" id="description" rows="3"
                                  class="form-control @error('description') is-invalid @enderror"
                                  placeholder="Short summary shown on the pricing page">{{ old('description', $plan->description) }}</textarea>
                        @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header">Pricing &amp; duration</div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label for="currency" class="form-label required">Currency</label>
                        <select name="currency" id="currency"
                                class="form-select @error('currency') is-invalid @enderror" required>
                            @foreach (['INR' => 'INR (₹)', 'USD' => 'USD ($)', 'EUR' => 'EUR (€)', 'GBP' => 'GBP (£)'] as $code => $label)
                                <option value="{{ $code }}"
                                    @selected(old('currency', $plan->currency ?? setting('default_currency', 'INR')) === $code)>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                        @error('currency') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-4">
                        <label for="amount" class="form-label required">Amount</label>
                        <input type="number" step="0.01" min="0" name="amount" id="amount"
                               class="form-control @error('amount') is-invalid @enderror"
                               value="{{ old('amount', $plan->amount ?? '0.00') }}" required>
                        @error('amount') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-5">
                        <label for="discount_amount" class="form-label">Discounted price</label>
                        <input type="number" step="0.01" min="0" name="discount_amount" id="discount_amount"
                               class="form-control @error('discount_amount') is-invalid @enderror"
                               value="{{ old('discount_amount', $plan->discount_amount) }}"
                               placeholder="Optional — must be lower than the amount">
                        @error('discount_amount') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-4">
                        <label for="duration_value" class="form-label required">Duration</label>
                        <input type="number" min="1" name="duration_value" id="duration_value"
                               class="form-control @error('duration_value') is-invalid @enderror"
                               value="{{ old('duration_value', $plan->duration_value ?? 1) }}">
                        @error('duration_value') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-4">
                        <label for="duration_unit" class="form-label required">Duration unit</label>
                        <select name="duration_unit" id="duration_unit"
                                class="form-select @error('duration_unit') is-invalid @enderror" required>
                            @foreach ($durationUnits as $value => $label)
                                <option value="{{ $value }}"
                                    @selected(old('duration_unit', $plan->duration_unit?->value) === $value)>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                        @error('duration_unit') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-4">
                        <label for="trial_days" class="form-label">Free trial (days)</label>
                        <input type="number" min="0" name="trial_days" id="trial_days"
                               class="form-control @error('trial_days') is-invalid @enderror"
                               value="{{ old('trial_days', $plan->trial_days ?? 0) }}">
                        @error('trial_days') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span>Features</span>
                <button type="button" class="btn btn-sm btn-outline-secondary" id="addFeature">
                    <i class="bi bi-plus-lg me-1"></i> Add feature
                </button>
            </div>
            <div class="card-body">
                <div id="featureList" class="d-flex flex-column gap-2">
                    @foreach ($features as $feature)
                        <div class="input-group feature-row">
                            <span class="input-group-text bg-light"><i class="bi bi-check2 text-success"></i></span>
                            <input type="text" name="features[]" class="form-control"
                                   value="{{ $feature }}" placeholder="e.g. Unlimited resume reviews">
                            <button type="button" class="btn btn-outline-danger remove-feature" aria-label="Remove">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    @endforeach
                </div>
                <div class="form-text">Blank rows are ignored when saving.</div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card mb-3">
            <div class="card-header">Availability</div>
            <div class="card-body">
                <div class="mb-3">
                    <label for="expiry_date" class="form-label">Expiry date</label>
                    <input type="date" name="expiry_date" id="expiry_date"
                           class="form-control @error('expiry_date') is-invalid @enderror"
                           value="{{ old('expiry_date', $plan->expiry_date?->format('Y-m-d')) }}">
                    @error('expiry_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    <div class="form-text">After this date the plan stops being offered. Leave blank for no expiry.</div>
                </div>

                <div class="mb-3">
                    <label for="sort_order" class="form-label">Display order</label>
                    <input type="number" min="0" name="sort_order" id="sort_order"
                           class="form-control @error('sort_order') is-invalid @enderror"
                           value="{{ old('sort_order', $plan->sort_order ?? 0) }}">
                    @error('sort_order') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <input type="hidden" name="is_active" value="0">
                <div class="form-check form-switch mb-2">
                    <input type="checkbox" name="is_active" id="is_active" value="1" class="form-check-input"
                           @checked(old('is_active', $plan->is_active ?? true))>
                    <label for="is_active" class="form-check-label">Active</label>
                </div>

                <input type="hidden" name="is_featured" value="0">
                <div class="form-check form-switch">
                    <input type="checkbox" name="is_featured" id="is_featured" value="1" class="form-check-input"
                           @checked(old('is_featured', $plan->is_featured ?? false))>
                    <label for="is_featured" class="form-check-label">Mark as most popular</label>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body d-flex flex-column gap-2">
                <button type="submit" class="btn btn-brand">
                    <i class="bi bi-save me-1"></i> {{ $plan->exists ? 'Update plan' : 'Create plan' }}
                </button>
                <a href="{{ route('admin.plans.index') }}" class="btn btn-light">Cancel</a>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    (function () {
        const list = document.getElementById('featureList');

        document.getElementById('addFeature').addEventListener('click', function () {
            const row = list.querySelector('.feature-row').cloneNode(true);
            row.querySelector('input').value = '';
            list.appendChild(row);
            row.querySelector('input').focus();
        });

        list.addEventListener('click', function (event) {
            const button = event.target.closest('.remove-feature');
            if (!button) return;

            if (list.querySelectorAll('.feature-row').length === 1) {
                button.closest('.feature-row').querySelector('input').value = '';
                return;
            }
            button.closest('.feature-row').remove();
        });

        // "Lifetime" plans have no numeric duration.
        const unit = document.getElementById('duration_unit');
        const value = document.getElementById('duration_value');

        function syncDuration() {
            const lifetime = unit.value === 'lifetime';
            value.disabled = lifetime;
            value.parentElement.classList.toggle('opacity-50', lifetime);
        }

        unit.addEventListener('change', syncDuration);
        syncDuration();
    })();
</script>
@endpush
