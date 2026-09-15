@extends('layouts.marketing')

@section('title', 'Plans')

@section('content')
@php
    $periodLabel = function (\App\Models\Plan $plan): ?string {
        if ($plan->isFree()) {
            return null;
        }

        return match ($plan->duration_unit) {
            \App\Enums\DurationUnit::Day => '/day',
            \App\Enums\DurationUnit::Week => '/week',
            \App\Enums\DurationUnit::Month => '/month',
            \App\Enums\DurationUnit::Year => '/year',
            default => null,
        };
    };

    $priceHtml = function (\App\Models\Plan $plan) use ($periodLabel): string {
        if ($plan->isFree()) {
            return 'Free';
        }

        $amount = (float) $plan->payable_amount;
        $formatted = $plan->currencySymbol().number_format($amount, fmod($amount, 1.0) === 0.0 ? 0 : 2);
        $period = $periodLabel($plan);

        return $period
            ? e($formatted).'<span>'.e($period).'</span>'
            : e($formatted);
    };
@endphp

<section class="pricing-section employer-panel">
    <div class="container">
        <div class="row">
            <div class="col-12 text-center">
                <h2 class="pricing-title mb-2">Plans that grow with your hiring</h2>
                <p class="pricing-subtitle mb-5">
                    Every plan includes applicant tracking, resume viewing and direct messaging.
                    Upgrade or<br class="d-none d-md-block">
                    change at any time.
                </p>
            </div>
        </div>

        @if ($subscription)
            <div class="row justify-content-center mb-4">
                <div class="col-12 col-lg-8">
                    <div class="application-card text-center">
                        <p class="application-meta mb-0">
                            Active plan:
                            <strong>{{ $subscription->plan?->title ?? 'Plan' }}</strong>
                            · {{ $subscription->remainingJobs() }} job(s) left
                            @if ($subscription->ends_at)
                                · expires {{ $subscription->ends_at->format('d M Y') }}
                            @endif
                        </p>
                    </div>
                </div>
            </div>
        @endif

        <div class="row justify-content-center g-4">
            @forelse ($plans as $plan)
                <div class="col-12 col-md-6 col-lg-3">
                    <div class="pricing-card h-100 {{ $plan->is_featured ? 'pricing-card-popular' : '' }}">
                        @if ($plan->is_featured)
                            <div class="popular-badge">Most popular</div>
                        @endif

                        <h3 class="plan-name">{{ $plan->title }}</h3>
                        <div class="plan-price">{!! $priceHtml($plan) !!}</div>
                        <p class="plan-description">
                            {{ $plan->description ?: 'Up to '.$plan->jobs_allowed.' active job post'.($plan->jobs_allowed === 1 ? '' : 's') }}
                        </p>

                        <ul class="plan-features">
                            <li>{{ $plan->jobs_allowed }} active job posting{{ $plan->jobs_allowed === 1 ? '' : 's' }}</li>
                            <li>Plan access: {{ $plan->duration_label }}</li>
                            <li>Each job live for: {{ $plan->job_duration_label }}</li>
                            @foreach ($plan->features ?? [] as $feature)
                                @continue(blank($feature))
                                <li>{{ $feature }}</li>
                            @endforeach
                        </ul>

                        <div class="plan-button-wrap">
                            <a href="{{ route('employer.plans.checkout', $plan) }}"
                               class="plan-btn {{ $plan->is_featured ? 'plan-btn-primary' : '' }}">
                                {{ $plan->isFree() ? 'Start free' : 'Choose plan' }}
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12 col-lg-6">
                    <div class="pricing-card text-center">
                        <p class="plan-description mb-0">No plans are available right now. Please check back later.</p>
                    </div>
                </div>
            @endforelse
        </div>
    </div>
</section>
@endsection
