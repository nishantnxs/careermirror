@extends('layouts.marketing')

@section('title', $plan->isFree() ? 'Activate plan' : 'Checkout')

@section('content')
<section class="employer-panel">
    <div class="container">
        <div class="mb-4">
            <a href="{{ route('employer.plans.index') }}" class="back-link">
                <i class="fa-solid fa-arrow-left me-1"></i> Back to plans
            </a>
            <h1 class="panel-title mt-2">{{ $plan->isFree() ? 'Activate free plan' : 'Secure checkout' }}</h1>
            <p class="panel-subtitle">
                {{ $plan->title }} · {{ $plan->formatted_amount }}
                @if ($plan->discount_percent)
                    <s class="text-secondary ms-1">{{ $plan->currencySymbol() }}{{ number_format((float) $plan->amount, 2) }}</s>
                    <span class="badge bg-success-subtle text-success-emphasis ms-1">Save {{ $plan->discount_percent }}%</span>
                @endif
                @if (! $plan->isFree() && config('payments.razorpay.mode') === 'test')
                    <span class="badge bg-warning-subtle text-warning-emphasis ms-1">Razorpay test mode</span>
                @endif
            </p>
        </div>

        <div class="row g-4">
            <div class="col-lg-5">
                <div class="panel-card">
                    <div class="panel-card-header">Plan summary</div>
                    <div class="panel-card-body">
                        <dl class="row mb-0 small">
                            @if ($plan->discount_percent)
                                <dt class="col-5 text-secondary fw-normal">Original price</dt>
                                <dd class="col-7">
                                    <s class="text-secondary">{{ $plan->currencySymbol() }}{{ number_format((float) $plan->amount, 2) }}</s>
                                </dd>
                                <dt class="col-5 text-secondary fw-normal">Discount</dt>
                                <dd class="col-7 text-success">Save {{ $plan->discount_percent }}%</dd>
                            @endif
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
                <div class="panel-card">
                    <div class="panel-card-header">{{ $plan->isFree() ? 'Confirm activation' : 'Choose payment method' }}</div>
                    <div class="panel-card-body">
                        <form method="POST" action="{{ route('employer.plans.purchase', $plan) }}">
                            @csrf

                            @unless ($plan->isFree())
                                <div class="mb-3">
                                    <div class="row g-3">
                                        @foreach ($gatewayOptions as $value => $label)
                                            <div class="col-md-6">
                                                <label class="payment-option {{ old('payment_mode', 'razorpay') === $value ? 'is-selected' : '' }}">
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

                            <button type="submit" class="btn btn-primary">
                                {{ $plan->isFree() ? 'Activate plan' : 'Continue to payment' }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
document.querySelectorAll('.payment-option input[type="radio"]').forEach((input) => {
    input.addEventListener('change', () => {
        document.querySelectorAll('.payment-option').forEach((option) => option.classList.remove('is-selected'));
        input.closest('.payment-option')?.classList.add('is-selected');
    });
});
</script>
@endpush
