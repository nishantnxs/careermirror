@extends('admin.layouts.app')

@section('title', $employer->company_name)
@section('heading', $employer->company_name)
@section('subheading', 'Employer account details and related activity.')

@section('actions')
    <a href="{{ route('admin.employers.index') }}" class="btn btn-light">
        <i class="bi bi-arrow-left me-1"></i> Back to employers
    </a>
@endsection

@section('content')
    <div class="row g-3">
        <div class="col-lg-7">
            <div class="card mb-3">
                <div class="card-header">Profile</div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-4 text-secondary fw-normal">Company</dt>
                        <dd class="col-sm-8">{{ $employer->company_name }}</dd>

                        <dt class="col-sm-4 text-secondary fw-normal">Contact name</dt>
                        <dd class="col-sm-8">{{ $employer->name }}</dd>

                        <dt class="col-sm-4 text-secondary fw-normal">Email</dt>
                        <dd class="col-sm-8">{{ $employer->email }}</dd>

                        <dt class="col-sm-4 text-secondary fw-normal">Phone</dt>
                        <dd class="col-sm-8">{{ $employer->phone ?: '—' }}</dd>

                        <dt class="col-sm-4 text-secondary fw-normal">Registered</dt>
                        <dd class="col-sm-8">{{ $employer->created_at->format('d M Y, h:i A') }}</dd>

                        <dt class="col-sm-4 text-secondary fw-normal">Last login</dt>
                        <dd class="col-sm-8 mb-0">
                            {{ $employer->last_login_at?->format('d M Y, h:i A') ?? 'Never' }}
                        </dd>
                    </dl>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header">Current subscription</div>
                <div class="card-body">
                    @if ($activeSubscription)
                        <dl class="row mb-0">
                            <dt class="col-sm-4 text-secondary fw-normal">Plan</dt>
                            <dd class="col-sm-8">{{ $activeSubscription->plan?->title ?? '—' }}</dd>

                            <dt class="col-sm-4 text-secondary fw-normal">Status</dt>
                            <dd class="col-sm-8">
                                <span class="badge {{ $activeSubscription->status->badgeClass() }}">
                                    {{ $activeSubscription->status->label() }}
                                </span>
                            </dd>

                            <dt class="col-sm-4 text-secondary fw-normal">Jobs</dt>
                            <dd class="col-sm-8">
                                {{ $activeSubscription->jobs_used }} / {{ $activeSubscription->jobs_allowed }} used
                                <span class="text-secondary">({{ $activeSubscription->remainingJobs() }} left)</span>
                            </dd>

                            <dt class="col-sm-4 text-secondary fw-normal">Starts</dt>
                            <dd class="col-sm-8">{{ $activeSubscription->starts_at->format('d M Y, h:i A') }}</dd>

                            <dt class="col-sm-4 text-secondary fw-normal">Expires</dt>
                            <dd class="col-sm-8 mb-0">
                                {{ $activeSubscription->ends_at?->format('d M Y, h:i A') ?? 'Lifetime' }}
                            </dd>
                        </dl>
                    @else
                        <p class="text-secondary small mb-0">No active subscription. Assign a plan below to grant access.</p>
                    @endif
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header">Orders &amp; payments</div>
                @if ($employer->orders->isNotEmpty())
                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Order</th>
                                    <th>Plan</th>
                                    <th>Mode</th>
                                    <th class="text-end">Amount</th>
                                    <th>Reference</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($employer->orders as $order)
                                    <tr>
                                        <td>
                                            <div class="fw-semibold">{{ $order->order_number }}</div>
                                            <div class="text-secondary" style="font-size:.75rem">
                                                {{ $order->paid_at?->format('d M Y') ?? $order->created_at->format('d M Y') }}
                                            </div>
                                        </td>
                                        <td>{{ $order->plan_title }}</td>
                                        <td class="text-secondary">{{ $order->payment_mode?->label() ?? '—' }}</td>
                                        <td class="text-end">{{ $order->formatted_final_amount }}</td>
                                        <td class="text-secondary small">
                                            {{ $order->transaction_reference ?: '—' }}
                                            @if ($order->payment_notes)
                                                <div>{{ \Illuminate\Support\Str::limit($order->payment_notes, 40) }}</div>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="card-body text-secondary small">No orders yet.</div>
                @endif
            </div>

            <div class="card mb-3">
                <div class="card-header">Job postings</div>
                @if ($employer->jobPostings->isNotEmpty())
                    <ul class="list-group list-group-flush">
                        @foreach ($employer->jobPostings as $job)
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="fw-semibold">{{ $job->title }}</div>
                                    <div class="text-secondary small">{{ $job->location ?: 'No location' }}</div>
                                </div>
                                <span class="badge {{ $job->status->badgeClass() }}">{{ $job->status->label() }}</span>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <div class="card-body text-secondary small">No job postings yet.</div>
                @endif
            </div>

            <div class="card">
                <div class="card-header">Applications</div>
                <div class="card-body text-secondary small">
                    No applications yet. Applications received for this employer’s jobs will appear here.
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card mb-3">
                <div class="card-header">Allowed domains</div>
                <div class="card-body">
                    <p class="text-secondary small">
                        These domains appear under “Show this job on” when the employer posts a job.
                    </p>
                    <form method="POST" action="{{ route('admin.employers.update-domains', $employer) }}">
                        @csrf
                        @method('PUT')
                        @php($selectedDomainIds = collect($selectedDomainIds ?? [])->map(fn ($id) => (int) $id)->all())
                        @forelse ($domains ?? [] as $domain)
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" name="domain_ids[]"
                                       id="employer_domain_{{ $domain->id }}" value="{{ $domain->id }}"
                                       @checked(in_array($domain->id, $selectedDomainIds, true))>
                                <label class="form-check-label" for="employer_domain_{{ $domain->id }}">
                                    {{ $domain->host }}
                                    <span class="text-secondary small">({{ $domain->displayName() }})</span>
                                </label>
                            </div>
                        @empty
                            <p class="text-danger small">No active domains available.</p>
                        @endforelse
                        @error('domain_ids') <div class="text-danger small mt-2">{{ $message }}</div> @enderror
                        <button type="submit" class="btn btn-brand w-100 mt-3">Save domains</button>
                    </form>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header">Assign plan</div>
                <div class="card-body">
                    @if ($plans->isEmpty())
                        <p class="text-secondary small mb-0">
                            No active plans available.
                            <a href="{{ route('admin.plans.create') }}">Create a plan</a> first.
                        </p>
                    @else
                        <form method="POST" action="{{ route('admin.employers.assign-plan', $employer) }}" class="row g-3">
                            @csrf

                            <div class="col-12">
                                <label for="plan_id" class="form-label required">Plan</label>
                                <select name="plan_id" id="plan_id"
                                        class="form-select @error('plan_id') is-invalid @enderror" required>
                                    <option value="">Select a plan</option>
                                    @foreach ($plans as $plan)
                                        <option value="{{ $plan->id }}"
                                            data-amount="{{ $plan->payable_amount }}"
                                            @selected((string) old('plan_id') === (string) $plan->id)>
                                            {{ $plan->title }}
                                            — {{ $plan->formatted_amount }}
                                            ({{ $plan->jobs_allowed }} jobs / {{ $plan->duration_label }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('plan_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="payment_amount" class="form-label required">Payment amount</label>
                                <input type="number" step="0.01" min="0" name="payment_amount" id="payment_amount"
                                       class="form-control @error('payment_amount') is-invalid @enderror"
                                       value="{{ old('payment_amount') }}" required>
                                @error('payment_amount') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                <div class="form-text">Enter 0 for complimentary access.</div>
                            </div>

                            <div class="col-md-6">
                                <label for="payment_mode" class="form-label required">Payment mode</label>
                                <select name="payment_mode" id="payment_mode"
                                        class="form-select @error('payment_mode') is-invalid @enderror" required>
                                    @foreach ($paymentModes as $value => $label)
                                        <option value="{{ $value }}"
                                            @selected(old('payment_mode', 'admin_assigned') === $value)>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('payment_mode') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-12">
                                <label for="transaction_reference" class="form-label">Reference number</label>
                                <input type="text" name="transaction_reference" id="transaction_reference"
                                       class="form-control @error('transaction_reference') is-invalid @enderror"
                                       value="{{ old('transaction_reference') }}"
                                       placeholder="UTR / cheque / receipt no.">
                                @error('transaction_reference') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-12">
                                <label for="payment_notes" class="form-label">Notes</label>
                                <textarea name="payment_notes" id="payment_notes" rows="3"
                                          class="form-control @error('payment_notes') is-invalid @enderror"
                                          placeholder="Optional payment or assignment notes">{{ old('payment_notes') }}</textarea>
                                @error('payment_notes') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-12">
                                <button type="submit" class="btn btn-brand w-100">
                                    <i class="bi bi-check2-circle me-1"></i> Assign plan
                                </button>
                            </div>
                        </form>
                    @endif
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header">Account status</div>
                <div class="card-body">
                    <div class="mb-3">
                        Current status:
                        <span class="badge {{ $employer->status->badgeClass() }}">{{ $employer->status->label() }}</span>
                    </div>

                    <form method="POST" action="{{ route('admin.employers.update-status', $employer) }}" class="row g-2">
                        @csrf
                        @method('PATCH')
                        <div class="col-12">
                            <label for="status" class="form-label">Change status</label>
                            <select name="status" id="status" class="form-select" required>
                                @foreach ($statuses as $value => $label)
                                    <option value="{{ $value }}" @selected($employer->status->value === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                            <div class="form-text">
                                Inactive deactivates the account. Suspended blocks access immediately.
                            </div>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-brand w-100">Update status</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-header">Activity</div>
                <ul class="list-group list-group-flush small">
                    <li class="list-group-item d-flex justify-content-between">
                        <span class="text-secondary">Account created</span>
                        <span>{{ $employer->created_at->diffForHumans() }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <span class="text-secondary">Last login</span>
                        <span>{{ $employer->last_login_at?->diffForHumans() ?? 'Never' }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <span class="text-secondary">Profile updated</span>
                        <span>{{ $employer->updated_at->diffForHumans() }}</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    (function () {
        const planSelect = document.getElementById('plan_id');
        const amountInput = document.getElementById('payment_amount');
        if (!planSelect || !amountInput) return;

        planSelect.addEventListener('change', function () {
            const option = planSelect.options[planSelect.selectedIndex];
            const amount = option?.dataset?.amount;
            if (amount !== undefined && amount !== '' && !amountInput.value) {
                amountInput.value = Number(amount).toFixed(2);
            }
        });
    })();
</script>
@endpush
