@extends('layouts.portal')

@section('title', $plan->isFree() ? 'Activate plan' : 'Checkout')
@section('account_label', auth('employer')->user()->company_name)
@section('logout_action', route('employer.logout'))

@section('nav')
    @include('employer.partials.nav', ['active' => 'plans'])
@endsection

@section('content')
    <div class="mb-4">
        <a href="{{ route('employer.plans.index') }}" class="text-secondary small text-decoration-none">
            <i class="bi bi-arrow-left me-1"></i> Back to plans
        </a>
        <h1 class="h4 fw-semibold mt-2 mb-1">{{ $plan->isFree() ? 'Activate free plan' : 'Secure checkout' }}</h1>
        <p class="text-secondary mb-0">
            {{ $plan->title }} &middot; {{ $plan->formatted_amount }}
            @if (! $plan->isFree() && config('payments.razorpay.mode') === 'test')
                <span class="badge bg-warning-subtle text-warning-emphasis ms-1">Razorpay test mode</span>
            @endif
        </p>
    </div>

    <div class="row g-3">
        <div class="col-lg-5">
            <div class="card">
                <div class="card-header">Plan summary</div>
                <div class="card-body">
                    <dl class="row mb-0 small">
                        <dt class="col-5 text-secondary fw-normal">Amount</dt>
                        <dd class="col-7 fw-semibold">{{ $plan->formatted_amount }}</dd>
                        <dt class="col-5 text-secondary fw-normal">Jobs allowed</dt>
                        <dd class="col-7">{{ $plan->jobs_allowed }}</dd>
                        <dt class="col-5 text-secondary fw-normal">Plan duration</dt>
                        <dd class="col-7">{{ $plan->duration_label }}</dd>
                        <dt class="col-5 text-secondary fw-normal">Job duration</dt>
                        <dd class="col-7 mb-0">{{ $plan->job_duration_label }}</dd>
                    </dl>
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="card">
                <div class="card-header">{{ $plan->isFree() ? 'Confirm activation' : 'Choose payment method' }}</div>
                <div class="card-body">
                    <form method="POST" action="{{ route('employer.plans.purchase', $plan) }}">
                        @csrf

                        @unless ($plan->isFree())
                            <div class="mb-3">
                                <div class="row g-2">
                                    @foreach ($gatewayOptions as $value => $label)
                                        <div class="col-md-6">
                                            <label class="border rounded p-3 w-100 h-100 {{ old('payment_mode') === $value ? 'border-brand' : '' }}">
                                                <input type="radio" class="form-check-input me-2"
                                                       name="payment_mode" value="{{ $value }}"
                                                       @checked(old('payment_mode', 'razorpay') === $value)
                                                       required>
                                                <span class="fw-semibold">{{ $label }}</span>
                                                <div class="text-secondary small mt-1">
                                                    @switch($value)
                                                        @case('paypal')
                                                            Pay with your PayPal account
                                                            @break
                                                        @case('stripe')
                                                            Cards via Stripe Checkout
                                                            @break
                                                        @case('razorpay')
                                                            Cards, netbanking &amp; wallets
                                                            @break
                                                        @case('upi')
                                                            Pay with UPI through Razorpay
                                                            @break
                                                    @endswitch
                                                </div>
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                                @error('payment_mode') <div class="text-danger small mt-2">{{ $message }}</div> @enderror
                            </div>
                        @else
                            <div class="mb-3">
                                <label for="payment_notes" class="form-label">Notes</label>
                                <textarea name="payment_notes" id="payment_notes" rows="3"
                                          class="form-control @error('payment_notes') is-invalid @enderror"
                                          placeholder="Optional notes">{{ old('payment_notes') }}</textarea>
                                @error('payment_notes') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        @endunless

                        <button type="submit" class="btn btn-brand">
                            {{ $plan->isFree() ? 'Activate plan' : 'Continue to payment' }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
