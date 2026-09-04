@extends('admin.layouts.app')

@section('title', 'Dashboard')
@section('heading', 'Dashboard')
@section('subheading', 'Overview of your plans and website configuration.')

@section('content')
    @php
        $cards = [
            ['label' => 'Total Plans', 'value' => $stats['total_plans'], 'icon' => 'bi-card-checklist', 'tone' => 'primary'],
            ['label' => 'Active Plans', 'value' => $stats['active_plans'], 'icon' => 'bi-check2-circle', 'tone' => 'success'],
            ['label' => 'Expiring in 30 days', 'value' => $stats['expiring_plans'], 'icon' => 'bi-hourglass-split', 'tone' => 'warning'],
            ['label' => 'Expired Plans', 'value' => $stats['expired_plans'], 'icon' => 'bi-x-octagon', 'tone' => 'danger'],
        ];
    @endphp

    <div class="row g-3 mb-4">
        @foreach ($cards as $card)
            <div class="col-6 col-xl-3">
                <div class="card stat-card h-100">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="stat-icon bg-{{ $card['tone'] }} bg-opacity-10 text-{{ $card['tone'] }}">
                            <i class="bi {{ $card['icon'] }}"></i>
                        </div>
                        <div>
                            <div class="h4 mb-0 fw-bold">{{ number_format($card['value']) }}</div>
                            <div class="text-secondary small">{{ $card['label'] }}</div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="row g-3">
        <div class="col-lg-8">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>Recently added plans</span>
                    <a href="{{ route('admin.plans.index') }}" class="btn btn-sm btn-outline-secondary">View all</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Type</th>
                                <th>Duration</th>
                                <th class="text-end">Amount</th>
                                <th class="text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($recentPlans as $plan)
                                <tr>
                                    <td>
                                        <a href="{{ route('admin.plans.edit', $plan) }}" class="fw-semibold text-dark">
                                            {{ $plan->title }}
                                        </a>
                                    </td>
                                    <td><span class="badge {{ $plan->plan_type->badgeClass() }}">{{ $plan->plan_type->label() }}</span></td>
                                    <td class="text-secondary">{{ $plan->duration_label }}</td>
                                    <td class="text-end fw-semibold">{{ $plan->formatted_amount }}</td>
                                    <td class="text-center">
                                        @if ($plan->is_active)
                                            <span class="badge bg-success-subtle text-success-emphasis">Active</span>
                                        @else
                                            <span class="badge bg-secondary-subtle text-secondary-emphasis">Inactive</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-secondary py-4">
                                        No plans yet.
                                        <a href="{{ route('admin.plans.create') }}" class="text-brand">Create the first one</a>.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-header">Quick actions</div>
                <div class="list-group list-group-flush">
                    <a href="{{ route('admin.plans.create') }}" class="list-group-item list-group-item-action d-flex align-items-center gap-3">
                        <i class="bi bi-plus-circle fs-5 text-brand"></i>
                        <div>
                            <div class="fw-semibold small">Add a new plan</div>
                            <div class="text-secondary" style="font-size:.78rem">Set pricing, duration and expiry</div>
                        </div>
                    </a>
                    <a href="{{ route('admin.settings.edit') }}" class="list-group-item list-group-item-action d-flex align-items-center gap-3">
                        <i class="bi bi-sliders fs-5 text-brand"></i>
                        <div>
                            <div class="fw-semibold small">Website settings</div>
                            <div class="text-secondary" style="font-size:.78rem">Logo, contact details, SEO and mail</div>
                        </div>
                    </a>
                </div>
                <div class="card-body border-top">
                    <div class="text-secondary small mb-1">Signed in as</div>
                    <div class="fw-semibold">{{ auth('admin')->user()->name }}</div>
                    <div class="text-secondary small">{{ auth('admin')->user()->email }}</div>
                    @if (auth('admin')->user()->last_login_at)
                        <div class="text-secondary mt-2" style="font-size:.78rem">
                            Last login {{ auth('admin')->user()->last_login_at->diffForHumans() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
