@extends('layouts.portal')

@section('title', 'Order '.$order->order_number)
@section('account_label', auth('employer')->user()->company_name)
@section('logout_action', route('employer.logout'))

@section('nav')
    @include('employer.partials.nav', ['active' => 'orders'])
@endsection

@section('content')
    <div class="mb-4">
        <a href="{{ route('employer.orders.index') }}" class="text-secondary small text-decoration-none">
            <i class="bi bi-arrow-left me-1"></i> Back to orders
        </a>
        <h1 class="h4 fw-semibold mt-2 mb-1">{{ $order->order_number }}</h1>
        <p class="text-secondary mb-0">Payment and subscription details for this purchase.</p>
    </div>

    <div class="row g-3">
        <div class="col-lg-7">
            <div class="card mb-3">
                <div class="card-header">Order</div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-4 text-secondary fw-normal">Plan</dt>
                        <dd class="col-sm-8">{{ $order->plan_title }}</dd>

                        <dt class="col-sm-4 text-secondary fw-normal">Amount</dt>
                        <dd class="col-sm-8">{{ $order->formatted_final_amount }}</dd>

                        <dt class="col-sm-4 text-secondary fw-normal">Jobs included</dt>
                        <dd class="col-sm-8">{{ $order->jobs_allowed }}</dd>

                        <dt class="col-sm-4 text-secondary fw-normal">Plan duration</dt>
                        <dd class="col-sm-8">
                            {{ $order->plan_duration_days ? $order->plan_duration_days.' days' : 'Lifetime' }}
                        </dd>

                        <dt class="col-sm-4 text-secondary fw-normal">Job duration</dt>
                        <dd class="col-sm-8 mb-0">
                            {{ $order->job_duration_days ? $order->job_duration_days.' days' : 'Lifetime' }}
                        </dd>
                    </dl>
                </div>
            </div>

            @if ($order->subscription)
                <div class="card">
                    <div class="card-header">Subscription</div>
                    <div class="card-body">
                        <dl class="row mb-0">
                            <dt class="col-sm-4 text-secondary fw-normal">Status</dt>
                            <dd class="col-sm-8">
                                <span class="badge {{ $order->subscription->status->badgeClass() }}">
                                    {{ $order->subscription->status->label() }}
                                </span>
                            </dd>
                            <dt class="col-sm-4 text-secondary fw-normal">Starts</dt>
                            <dd class="col-sm-8">{{ $order->subscription->starts_at->format('d M Y, h:i A') }}</dd>
                            <dt class="col-sm-4 text-secondary fw-normal">Expires</dt>
                            <dd class="col-sm-8">
                                {{ $order->subscription->ends_at?->format('d M Y, h:i A') ?? 'Lifetime' }}
                            </dd>
                            <dt class="col-sm-4 text-secondary fw-normal">Jobs used</dt>
                            <dd class="col-sm-8 mb-0">
                                {{ $order->subscription->jobs_used }} / {{ $order->subscription->jobs_allowed }}
                            </dd>
                        </dl>
                    </div>
                </div>
            @endif
        </div>

        <div class="col-lg-5">
            <div class="card">
                <div class="card-header">Payment audit trail</div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-5 text-secondary fw-normal">Status</dt>
                        <dd class="col-7">
                            <span class="badge {{ $order->payment_status->badgeClass() }}">
                                {{ $order->payment_status->label() }}
                            </span>
                        </dd>

                        <dt class="col-5 text-secondary fw-normal">Mode</dt>
                        <dd class="col-7">{{ $order->payment_mode?->label() ?? '—' }}</dd>

                        <dt class="col-5 text-secondary fw-normal">Reference</dt>
                        <dd class="col-7">{{ $order->transaction_reference ?: '—' }}</dd>

                        <dt class="col-5 text-secondary fw-normal">Paid at</dt>
                        <dd class="col-7">{{ $order->paid_at?->format('d M Y, h:i A') ?? '—' }}</dd>

                        <dt class="col-5 text-secondary fw-normal">Notes</dt>
                        <dd class="col-7">{{ $order->payment_notes ?: '—' }}</dd>

                        @if (filled($order->payment_meta))
                            <dt class="col-5 text-secondary fw-normal">Meta</dt>
                            <dd class="col-7">
                                <ul class="list-unstyled small mb-0">
                                    @foreach ($order->payment_meta as $key => $value)
                                        <li><span class="text-secondary">{{ str_replace('_', ' ', $key) }}:</span> {{ is_scalar($value) ? $value : json_encode($value) }}</li>
                                    @endforeach
                                </ul>
                            </dd>
                        @endif
                    </dl>
                </div>
            </div>

            <div class="mt-3">
                <a href="{{ route('employer.jobs.create') }}" class="btn btn-brand">Post a job</a>
            </div>
        </div>
    </div>
@endsection
