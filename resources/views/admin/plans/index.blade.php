@extends('admin.layouts.app')

@section('title', 'Plans')
@section('heading', 'Plans')
@section('subheading', 'Manage subscription plans, pricing and availability.')

@section('actions')
    <a href="{{ route('admin.plans.create') }}" class="btn btn-brand">
        <i class="bi bi-plus-lg me-1"></i> Add Plan
    </a>
@endsection

@section('content')
    <div class="card mb-3">
        <div class="card-body py-3">
            <form method="GET" action="{{ route('admin.plans.index') }}" class="row g-2 align-items-end">
                <div class="col-md-5">
                    <label for="search" class="form-label">Search</label>
                    <input type="search" name="search" id="search" class="form-control"
                           value="{{ request('search') }}" placeholder="Title or description">
                </div>
                <div class="col-md-3">
                    <label for="plan_type" class="form-label">Plan type</label>
                    <select name="plan_type" id="plan_type" class="form-select">
                        <option value="">All types</option>
                        @foreach ($planTypes as $value => $label)
                            <option value="{{ $value }}" @selected(request('plan_type') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="status" class="form-label">Status</label>
                    <select name="status" id="status" class="form-select">
                        <option value="">All</option>
                        <option value="active" @selected(request('status') === 'active')>Active</option>
                        <option value="inactive" @selected(request('status') === 'inactive')>Inactive</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-brand w-100">
                        <i class="bi bi-funnel"></i>
                    </button>
                    @if (request()->hasAny(['search', 'plan_type', 'status']))
                        <a href="{{ route('admin.plans.index') }}" class="btn btn-light" title="Reset">
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
                        <th style="width:60px">#</th>
                        <th>Plan</th>
                        <th>Type</th>
                        <th>Domains</th>
                        <th>Duration</th>
                        <th class="text-center">Jobs</th>
                        <th class="text-end">Amount</th>
                        <th>Expiry</th>
                        <th class="text-center">Status</th>
                        <th class="text-end" style="width:130px">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($plans as $plan)
                        <tr>
                            <td class="text-secondary">{{ $plan->sort_order }}</td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <a href="{{ route('admin.plans.edit', $plan) }}" class="fw-semibold text-dark">
                                        {{ $plan->title }}
                                    </a>
                                    @if ($plan->is_featured)
                                        <span class="badge bg-warning-subtle text-warning-emphasis">Popular</span>
                                    @endif
                                </div>
                                <div class="text-secondary" style="font-size:.78rem">{{ $plan->slug }}</div>
                            </td>
                            <td>
                                <span class="badge {{ $plan->plan_type->badgeClass() }}">
                                    {{ $plan->plan_type->label() }}
                                </span>
                            </td>
                            <td>
                                @forelse ($plan->domains as $domain)
                                    <span class="badge bg-light text-dark border">{{ $domain->host }}</span>
                                @empty
                                    <span class="text-secondary">—</span>
                                @endforelse
                            </td>
                            <td class="text-secondary">
                                {{ $plan->duration_label }}
                                @if ($plan->trial_days > 0)
                                    <div style="font-size:.78rem">+ {{ $plan->trial_days }}d trial</div>
                                @endif
                            </td>
                            <td class="text-center text-secondary">{{ $plan->jobs_allowed }}</td>
                            <td class="text-end">
                                <span class="fw-semibold">{{ $plan->formatted_amount }}</span>
                                @if ($plan->discount_percent)
                                    <div style="font-size:.78rem">
                                        <s class="text-secondary">{{ $plan->currencySymbol() }}{{ number_format((float) $plan->amount, 2) }}</s>
                                        <span class="text-success">-{{ $plan->discount_percent }}%</span>
                                    </div>
                                @endif
                            </td>
                            <td>
                                @if ($plan->expiry_date)
                                    <span class="{{ $plan->is_expired ? 'text-danger' : 'text-secondary' }}">
                                        {{ $plan->expiry_date->format('d M Y') }}
                                    </span>
                                    @if ($plan->is_expired)
                                        <div class="badge bg-danger-subtle text-danger-emphasis">Expired</div>
                                    @endif
                                @else
                                    <span class="text-secondary">—</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <form method="POST" action="{{ route('admin.plans.toggle-status', $plan) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit"
                                            class="btn btn-sm border-0 badge {{ $plan->is_active ? 'bg-success-subtle text-success-emphasis' : 'bg-secondary-subtle text-secondary-emphasis' }}"
                                            title="Click to toggle">
                                        {{ $plan->is_active ? 'Active' : 'Inactive' }}
                                    </button>
                                </form>
                            </td>
                            <td class="text-end text-nowrap">
                                <a href="{{ route('admin.plans.show', $plan) }}"
                                   class="btn btn-sm btn-light" title="View"><i class="bi bi-eye"></i></a>
                                <a href="{{ route('admin.plans.edit', $plan) }}"
                                   class="btn btn-sm btn-light" title="Edit"><i class="bi bi-pencil"></i></a>
                                <button type="button" class="btn btn-sm btn-light text-danger" title="Delete"
                                        data-bs-toggle="modal" data-bs-target="#deletePlanModal"
                                        data-plan-title="{{ $plan->title }}"
                                        data-plan-url="{{ route('admin.plans.destroy', $plan) }}">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center text-secondary py-5">
                                <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                                No plans found.
                                <a href="{{ route('admin.plans.create') }}" class="text-brand">Add your first plan</a>.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($plans->hasPages())
            <div class="card-footer bg-white">
                {{ $plans->links() }}
            </div>
        @endif
    </div>

    <div class="modal fade" id="deletePlanModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Delete plan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to delete <strong id="deletePlanTitle"></strong>?
                    You can restore it later from the database if needed.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <form method="POST" id="deletePlanForm">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Delete</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    document.getElementById('deletePlanModal')?.addEventListener('show.bs.modal', function (event) {
        const trigger = event.relatedTarget;
        document.getElementById('deletePlanTitle').textContent = trigger.dataset.planTitle;
        document.getElementById('deletePlanForm').setAttribute('action', trigger.dataset.planUrl);
    });
</script>
@endpush
