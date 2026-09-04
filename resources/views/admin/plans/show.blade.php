@extends('admin.layouts.app')

@section('title', 'Plan Details')
@section('heading', $plan->title)
@section('subheading', 'Plan details and pricing summary.')

@section('actions')
    <a href="{{ route('admin.plans.edit', $plan) }}" class="btn btn-brand">
        <i class="bi bi-pencil me-1"></i> Edit
    </a>
    <a href="{{ route('admin.plans.index') }}" class="btn btn-light">
        <i class="bi bi-arrow-left me-1"></i> Back to plans
    </a>
@endsection

@section('content')
    <div class="row g-3">
        <div class="col-lg-7">
            <div class="card mb-3">
                <div class="card-header">Overview</div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-4 text-secondary fw-normal">Plan type</dt>
                        <dd class="col-sm-8">
                            <span class="badge {{ $plan->plan_type->badgeClass() }}">{{ $plan->plan_type->label() }}</span>
                        </dd>

                        <dt class="col-sm-4 text-secondary fw-normal">Slug</dt>
                        <dd class="col-sm-8"><code>{{ $plan->slug }}</code></dd>

                        <dt class="col-sm-4 text-secondary fw-normal">Duration</dt>
                        <dd class="col-sm-8">
                            {{ $plan->duration_label }}
                            @if ($plan->duration_in_days)
                                <span class="text-secondary small">({{ $plan->duration_in_days }} days)</span>
                            @endif
                        </dd>

                        <dt class="col-sm-4 text-secondary fw-normal">Free trial</dt>
                        <dd class="col-sm-8">{{ $plan->trial_days > 0 ? $plan->trial_days.' days' : 'None' }}</dd>

                        <dt class="col-sm-4 text-secondary fw-normal">Expiry date</dt>
                        <dd class="col-sm-8">
                            @if ($plan->expiry_date)
                                {{ $plan->expiry_date->format('d M Y') }}
                                @if ($plan->is_expired)
                                    <span class="badge bg-danger-subtle text-danger-emphasis">Expired</span>
                                @endif
                            @else
                                <span class="text-secondary">No expiry</span>
                            @endif
                        </dd>

                        <dt class="col-sm-4 text-secondary fw-normal">Status</dt>
                        <dd class="col-sm-8">
                            @if ($plan->is_active)
                                <span class="badge bg-success-subtle text-success-emphasis">Active</span>
                            @else
                                <span class="badge bg-secondary-subtle text-secondary-emphasis">Inactive</span>
                            @endif
                            @if ($plan->is_featured)
                                <span class="badge bg-warning-subtle text-warning-emphasis">Most popular</span>
                            @endif
                        </dd>

                        <dt class="col-sm-4 text-secondary fw-normal">Created</dt>
                        <dd class="col-sm-8 mb-0">{{ $plan->created_at->format('d M Y, h:i A') }}</dd>
                    </dl>
                </div>
            </div>

            @if ($plan->description)
                <div class="card">
                    <div class="card-header">Description</div>
                    <div class="card-body text-secondary">{{ $plan->description }}</div>
                </div>
            @endif
        </div>

        <div class="col-lg-5">
            <div class="card mb-3">
                <div class="card-header">Pricing</div>
                <div class="card-body text-center py-4">
                    <div class="display-6 fw-bold text-brand">{{ $plan->formatted_amount }}</div>
                    @if ($plan->discount_percent)
                        <div class="mt-1">
                            <s class="text-secondary">{{ $plan->currencySymbol() }}{{ number_format((float) $plan->amount, 2) }}</s>
                            <span class="badge bg-success-subtle text-success-emphasis ms-1">
                                Save {{ $plan->discount_percent }}%
                            </span>
                        </div>
                    @endif
                    <div class="text-secondary small mt-2">per {{ $plan->duration_label }}</div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">Features</div>
                @if (filled($plan->features))
                    <ul class="list-group list-group-flush">
                        @foreach ($plan->features as $feature)
                            <li class="list-group-item d-flex align-items-start gap-2">
                                <i class="bi bi-check-circle-fill text-success mt-1"></i>
                                <span>{{ $feature }}</span>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <div class="card-body text-secondary small">No features listed for this plan.</div>
                @endif
            </div>
        </div>
    </div>
@endsection
