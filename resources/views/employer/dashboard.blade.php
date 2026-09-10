@extends('layouts.portal')

@section('title', 'Employer Dashboard')
@section('account_label', auth('employer')->user()->company_name)
@section('logout_action', route('employer.logout'))
@section('home_href', route('employer.dashboard'))

@section('nav')
    @include('employer.partials.nav', ['active' => 'dashboard'])
@endsection

@section('content')
    @php
        $subscription = auth('employer')->user()->subscriptions()->active()->with('plan')->latest('id')->first();
    @endphp

    <section class="portal-welcome">
        <div>
            <h1>Welcome, {{ auth('employer')->user()->name }}</h1>
            <p class="text-secondary mb-0">
                Signed in for {{ auth('employer')->user()->company_name }}.
                @if ($subscription)
                    Your {{ $subscription->plan?->title }} plan has {{ $subscription->remainingJobs() }} job credit(s) left
                    @if ($subscription->ends_at)
                        and expires on {{ $subscription->ends_at->format('d M Y') }}
                    @endif.
                @else
                    Choose a plan to start posting jobs.
                @endif
            </p>
        </div>

        @if ($subscription)
            <span class="portal-badge">
                <i class="bi bi-check-circle"></i>
                {{ $subscription->remainingJobs() }} credit(s) left
            </span>
        @else
            <a href="{{ route('employer.plans.index') }}" class="portal-btn portal-btn-sm">Browse plans</a>
        @endif
    </section>

    <div class="portal-actions">
        <a href="{{ route('employer.plans.index') }}" class="portal-action-card">
            <div class="portal-action-icon"><i class="bi bi-tags"></i></div>
            <h2>Browse plans</h2>
            <p>View free and paid plans created by admin.</p>
        </a>

        <a href="{{ route('employer.jobs.index') }}" class="portal-action-card">
            <div class="portal-action-icon"><i class="bi bi-briefcase"></i></div>
            <h2>Manage jobs</h2>
            <p>Post and manage openings with your plan credits.</p>
        </a>

        <a href="{{ route('employer.orders.index') }}" class="portal-action-card">
            <div class="portal-action-icon"><i class="bi bi-receipt"></i></div>
            <h2>Payment history</h2>
            <p>Review payment mode and transaction audit details.</p>
        </a>
    </div>
@endsection
