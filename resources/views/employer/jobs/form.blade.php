@php
    $selectedCategoryId = old('category_id', $job->category_id);
    $isOther = old('category_selection', $job->category && ! $job->category->isApproved() ? 'other' : 'listed') === 'other'
        || (old('category_selection') === null && filled(old('custom_category_name')));
    if (old('category_selection') === 'listed') {
        $isOther = false;
    }
    if (old('category_selection') === 'other') {
        $isOther = true;
    }
@endphp

<div class="row g-3">
    <div class="col-12">
        <label for="title" class="form-label required">Job title</label>
        <input type="text" name="title" id="title"
               class="form-control @error('title') is-invalid @enderror"
               value="{{ old('title', $job->title) }}" required>
        @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-12">
        <label for="description" class="form-label required">Description</label>
        <textarea name="description" id="description" rows="6"
                  class="form-control @error('description') is-invalid @enderror" required>{{ old('description', $job->description) }}</textarea>
        @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-12">
        <label class="form-label required">Popular categories</label>
        <input type="hidden" name="category_selection" id="category_selection" value="{{ $isOther ? 'other' : 'listed' }}">
        <input type="hidden" name="category_id" id="category_id" value="{{ $isOther ? '' : $selectedCategoryId }}">

        <div class="d-flex flex-wrap gap-2" id="categoryChips" role="group" aria-label="Job categories">
            @foreach ($categories as $category)
                <button type="button"
                        class="btn btn-sm category-chip {{ ! $isOther && (string) $selectedCategoryId === (string) $category->id ? 'active' : '' }}"
                        data-category-id="{{ $category->id }}">
                    {{ $category->name }}
                </button>
            @endforeach
            <button type="button"
                    class="btn btn-sm category-chip {{ $isOther ? 'active' : '' }}"
                    data-category-id="other"
                    id="otherCategoryChip">
                Other
            </button>
        </div>

        @error('category_id')
            <div class="text-danger small mt-2">{{ $message }}</div>
        @enderror

        <div id="otherCategoryWrap" class="mt-3 {{ $isOther ? '' : 'd-none' }}">
            <label for="custom_category_name" class="form-label required">Your category name</label>
            <input type="text" name="custom_category_name" id="custom_category_name"
                   class="form-control @error('custom_category_name') is-invalid @enderror"
                   value="{{ old('custom_category_name', $isOther ? $job->category?->name : '') }}"
                   maxlength="120"
                   placeholder="Type a category name"
                   autocomplete="off">
            <div id="customCategoryFeedback" class="form-text mt-1"></div>
            @error('custom_category_name') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
        </div>
    </div>

    <div class="col-12">
        <label class="form-label required">Show this job on</label>
        @php
            $selectedDomainIds = collect(old('domain_ids', $job->relationLoaded('domains') ? $job->domains->pluck('id')->all() : []))
                ->map(fn ($id) => (int) $id)
                ->all();
            if ($selectedDomainIds === [] && isset($availableDomains) && $availableDomains->count() === 1) {
                $selectedDomainIds = [$availableDomains->first()->id];
            }
        @endphp
        <div class="d-flex flex-column gap-1">
            @forelse ($availableDomains ?? [] as $domain)
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" name="domain_ids[]" id="job_domain_{{ $domain->id }}"
                           value="{{ $domain->id }}" @checked(in_array($domain->id, $selectedDomainIds, true))>
                    <label class="form-check-label" for="job_domain_{{ $domain->id }}">
                        {{ $domain->host }}
                        <span class="text-secondary small">({{ $domain->displayName() }})</span>
                    </label>
                </div>
            @empty
                <p class="text-danger small mb-0">No domains are assigned to your employer account. Contact support.</p>
            @endforelse
        </div>
        @error('domain_ids') <div class="text-danger small mt-2">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6">
        <label for="location" class="form-label">Location</label>
        <input type="text" name="location" id="location"
               class="form-control @error('location') is-invalid @enderror"
               value="{{ old('location', $job->location) }}">
        @error('location') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-3">
        <label for="job_type" class="form-label">Job type</label>
        <input type="text" name="job_type" id="job_type"
               class="form-control @error('job_type') is-invalid @enderror"
               value="{{ old('job_type', $job->job_type) }}" placeholder="full-time">
        @error('job_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-3">
        <label for="experience_level" class="form-label">Experience</label>
        <input type="text" name="experience_level" id="experience_level"
               class="form-control @error('experience_level') is-invalid @enderror"
               value="{{ old('experience_level', $job->experience_level) }}" placeholder="mid">
        @error('experience_level') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-3">
        <label for="currency" class="form-label required">Currency</label>
        <select name="currency" id="currency" class="form-select @error('currency') is-invalid @enderror" required>
            @foreach (['INR', 'USD', 'EUR', 'GBP'] as $code)
                <option value="{{ $code }}" @selected(old('currency', $job->currency ?? 'INR') === $code)>{{ $code }}</option>
            @endforeach
        </select>
        @error('currency') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-3">
        <label for="salary_min" class="form-label">Salary min</label>
        <input type="number" step="0.01" min="0" name="salary_min" id="salary_min"
               class="form-control @error('salary_min') is-invalid @enderror"
               value="{{ old('salary_min', $job->salary_min) }}">
        @error('salary_min') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-3">
        <label for="salary_max" class="form-label">Salary max</label>
        <input type="number" step="0.01" min="0" name="salary_max" id="salary_max"
               class="form-control @error('salary_max') is-invalid @enderror"
               value="{{ old('salary_max', $job->salary_max) }}">
        @error('salary_max') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-3">
        <label for="status" class="form-label required">Status</label>
        <select name="status" id="status" class="form-select @error('status') is-invalid @enderror" required>
            @foreach (\App\Enums\JobStatus::cases() as $status)
                <option value="{{ $status->value }}"
                    @selected(old('status', $job->status?->value ?? 'published') === $status->value)>
                    {{ $status->label() }}
                </option>
            @endforeach
        </select>
        @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>

@push('styles')
<style>
    .category-chip {
        border-radius: 999px;
        border: 1px solid #d7dde5;
        background: #fff;
        color: #334155;
        padding: .35rem .9rem;
    }
    .category-chip:hover {
        border-color: #94a3b8;
        background: #f8fafc;
    }
    .category-chip.active {
        border-color: #0f766e;
        background: #ecfdf5;
        color: #0f766e;
        font-weight: 600;
    }
</style>
@endpush

@push('scripts')
<script>
(() => {
    const chips = document.querySelectorAll('.category-chip');
    const selectionInput = document.getElementById('category_selection');
    const categoryIdInput = document.getElementById('category_id');
    const otherWrap = document.getElementById('otherCategoryWrap');
    const customInput = document.getElementById('custom_category_name');
    const feedback = document.getElementById('customCategoryFeedback');
    const checkUrl = @json(route('employer.categories.check'));
    let timer = null;

    const setActive = (button) => {
        chips.forEach((chip) => chip.classList.remove('active'));
        button.classList.add('active');
    };

    chips.forEach((chip) => {
        chip.addEventListener('click', () => {
            setActive(chip);
            const value = chip.dataset.categoryId;

            if (value === 'other') {
                selectionInput.value = 'other';
                categoryIdInput.value = '';
                otherWrap.classList.remove('d-none');
                customInput.focus();
                return;
            }

            selectionInput.value = 'listed';
            categoryIdInput.value = value;
            otherWrap.classList.add('d-none');
            feedback.textContent = '';
            feedback.className = 'form-text mt-1';
            customInput.classList.remove('is-invalid');
        });
    });

    const renderFeedback = (payload) => {
        feedback.textContent = payload.message || '';
        feedback.className = 'form-text mt-1';
        customInput.classList.remove('is-invalid');

        if (payload.status === 'exists_approved') {
            feedback.classList.add('text-danger');
            customInput.classList.add('is-invalid');
        } else if (payload.status === 'exists_pending') {
            feedback.classList.add('text-warning');
        } else if (payload.status === 'available') {
            feedback.classList.add('text-success');
        } else if (payload.status === 'exists_rejected') {
            feedback.classList.add('text-secondary');
        }
    };

    customInput?.addEventListener('input', () => {
        const name = customInput.value.trim();
        clearTimeout(timer);
        feedback.textContent = '';
        customInput.classList.remove('is-invalid');

        if (name.length < 2) {
            return;
        }

        timer = setTimeout(async () => {
            try {
                const response = await fetch(`${checkUrl}?name=${encodeURIComponent(name)}`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });

                if (!response.ok) {
                    return;
                }

                renderFeedback(await response.json());
            } catch (error) {
                // Ignore network errors while typing.
            }
        }, 300);
    });
})();
</script>
@endpush
