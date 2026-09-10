@extends('layouts.portal')

@section('title', 'Orders')
@section('account_label', auth('employer')->user()->company_name)
@section('logout_action', route('employer.logout'))

@section('nav')
    @include('employer.partials.nav', ['active' => 'orders'])
@endsection

@section('content')
    <div class="mb-4">
        <h1 class="h4 fw-semibold mb-1">Payment history</h1>
        <p class="text-secondary mb-0">Audit trail of plan purchases and payment details.</p>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Order</th>
                        <th>Plan</th>
                        <th>Mode</th>
                        <th class="text-end">Amount</th>
                        <th>Status</th>
                        <th>Paid at</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($orders as $order)
                        <tr>
                            <td>
                                <a href="{{ route('employer.orders.show', $order) }}" class="fw-semibold text-dark">
                                    {{ $order->order_number }}
                                </a>
                            </td>
                            <td>{{ $order->plan_title }}</td>
                            <td class="text-secondary">{{ $order->payment_mode?->label() ?? '—' }}</td>
                            <td class="text-end">{{ $order->formatted_final_amount }}</td>
                            <td>
                                <span class="badge {{ $order->payment_status->badgeClass() }}">
                                    {{ $order->payment_status->label() }}
                                </span>
                            </td>
                            <td class="text-secondary">
                                {{ $order->paid_at?->format('d M Y, h:i A') ?? '—' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-secondary py-4">No purchases yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($orders->hasPages())
            <div class="card-footer">{{ $orders->links() }}</div>
        @endif
    </div>
@endsection
