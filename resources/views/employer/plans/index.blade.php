@extends('layouts.portal')

@section('title', 'Plans')
@section('account_label', auth('employer')->user()->company_name)
@section('logout_action', route('employer.logout'))

@section('nav')
    @include('employer.partials.nav', ['active' => 'plans'])
@endsection

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-4">
        <div>
            <h1 class="h4 fw-semibold mb-1">Subscription plans</h1>
            <p class="text-secondary mb-0">Choose a plan to unlock job posting credits.</p>
        </div>
        @if ($subscription)
            <div class="badge {{ $subscription->status->badgeClass() }} p-2">
                Active: {{ $subscription->plan?->title ?? 'Plan' }}
                &middot; {{ $subscription->remainingJobs() }} job(s) left
                @if ($subscription->ends_at)
                    &middot; expires {{ $subscription->ends_at->format('d M Y') }}
                @endif
            </div>
        @endif
    </div>

    <div class="row g-3">
        @forelse ($plans as $plan)
            <div class="col-md-6">
                <div class="card h-100 {{ $plan->is_featured ? 'border-brand' : '' }}">
                    <div class="card-body d-flex flex-column">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <span class="badge {{ $plan->plan_type->badgeClass() }} mb-2">{{ $plan->plan_type->label() }}</span>
                                <h2 class="h5 fw-semibold mb-0">{{ $plan->title }}</h2>
                            </div>
                            @if ($plan->is_featured)
                                <span class="badge bg-warning-subtle text-warning-emphasis">Popular</span>
                            @endif
                        </div>

                        <div class="display-6 fw-bold text-brand my-3">{{ $plan->formatted_amount }}</div>
                        <p class="text-secondary small mb-3">{{ $plan->description }}</p>

                        <ul class="list-unstyled small text-secondary mb-4">
                            <li class="mb-1"><i class="bi bi-briefcase me-1"></i> {{ $plan->jobs_allowed }} job posting(s)</li>
                            <li class="mb-1"><i class="bi bi-clock-history me-1"></i> Plan access: {{ $plan->duration_label }}</li>
                            <li class="mb-1"><i class="bi bi-calendar-event me-1"></i> Each job live for: {{ $plan->job_duration_label }}</li>
                        </ul>

                        @if (filled($plan->features))
                            <ul class="list-unstyled small mb-4">
                                @foreach ($plan->features as $feature)
                                    <li class="mb-1"><i class="bi bi-check2 text-success me-1"></i> {{ $feature }}</li>
                                @endforeach
                            </ul>
                        @endif

                        <div class="mt-auto">
                            <a href="{{ route('employer.plans.checkout', $plan) }}" class="btn btn-brand w-100">
                                {{ $plan->isFree() ? 'Activate free plan' : 'Buy this plan' }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="card">
                    <div class="card-body text-secondary">No plans are available right now. Please check back later.</div>
                </div>
            </div>
        @endforelse
    </div>
@endsection
