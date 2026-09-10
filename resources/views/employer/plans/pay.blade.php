@extends('layouts.portal')

@section('title', 'Complete payment')
@section('account_label', auth('employer')->user()->company_name)
@section('logout_action', route('employer.logout'))

@section('nav')
    @include('employer.partials.nav', ['active' => 'plans'])
@endsection

@section('content')
    <div class="card">
        <div class="card-body p-4 text-center">
            <h1 class="h4 fw-semibold mb-2">Complete your payment</h1>
            <p class="text-secondary mb-1">{{ $plan->title }} &middot; {{ $order->formatted_final_amount }}</p>
            <p class="text-secondary small mb-4">Order {{ $order->order_number }}</p>

            @if (($checkout->payload['gateway'] ?? '') === 'razorpay')
                <button type="button" id="payWithRazorpay" class="btn btn-brand btn-lg">
                    Pay now
                </button>
                <p class="text-secondary small mt-3 mb-0">You will be redirected to Razorpay to finish payment securely.</p>
            @else
                <p class="text-secondary mb-0">Preparing payment…</p>
            @endif
        </div>
    </div>
@endsection

@if (($checkout->payload['gateway'] ?? '') === 'razorpay')
    @push('scripts')
        <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
        <script>
            (function () {
                const options = @json($checkout->payload);
                const confirmUrl = options.confirm_url;
                const csrf = document.querySelector('meta[name="csrf-token"]').content;

                function openCheckout() {
                    const rzpOptions = {
                        key: options.key,
                        amount: options.amount,
                        currency: options.currency,
                        name: options.name,
                        description: options.description,
                        order_id: options.order_id,
                        prefill: options.prefill || {},
                        notes: options.notes || {},
                        theme: options.theme || {},
                        handler: function (response) {
                            const form = document.createElement('form');
                            form.method = 'POST';
                            form.action = confirmUrl;

                            const token = document.createElement('input');
                            token.type = 'hidden';
                            token.name = '_token';
                            token.value = csrf;
                            form.appendChild(token);

                            ['razorpay_payment_id', 'razorpay_order_id', 'razorpay_signature'].forEach(function (field) {
                                const input = document.createElement('input');
                                input.type = 'hidden';
                                input.name = field;
                                input.value = response[field];
                                form.appendChild(input);
                            });

                            document.body.appendChild(form);
                            form.submit();
                        }
                    };

                    if (options.method) {
                        rzpOptions.method = options.method;
                    }

                    const rzp = new Razorpay(rzpOptions);
                    rzp.open();
                }

                document.getElementById('payWithRazorpay').addEventListener('click', openCheckout);
                openCheckout();
            })();
        </script>
    @endpush
@endif
