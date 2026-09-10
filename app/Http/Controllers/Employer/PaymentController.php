<?php

namespace App\Http\Controllers\Employer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\PlanPurchaseService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use RuntimeException;
use Throwable;

class PaymentController extends Controller
{
    public function fakeComplete(Order $order, PlanPurchaseService $purchases): RedirectResponse
    {
        return $this->complete($order, $purchases, []);
    }

    public function razorpayConfirm(Request $request, Order $order, PlanPurchaseService $purchases): RedirectResponse
    {
        $payload = $request->validate([
            'razorpay_payment_id' => ['required', 'string'],
            'razorpay_order_id' => ['required', 'string'],
            'razorpay_signature' => ['required', 'string'],
        ]);

        return $this->complete($order, $purchases, $payload);
    }

    public function stripeSuccess(Request $request, Order $order, PlanPurchaseService $purchases): RedirectResponse
    {
        return $this->complete($order, $purchases, [
            'session_id' => $request->query('session_id'),
        ]);
    }

    public function stripeCancel(Order $order): RedirectResponse
    {
        abort_unless($order->employer_id === auth('employer')->id(), 404);

        return redirect()
            ->route('employer.plans.checkout', $order->plan_id)
            ->with('error', 'Stripe payment was cancelled.');
    }

    public function paypalReturn(Request $request, Order $order, PlanPurchaseService $purchases): RedirectResponse
    {
        return $this->complete($order, $purchases, [
            'token' => $request->query('token'),
            'paypal_order_id' => $request->query('token'),
        ]);
    }

    public function paypalCancel(Order $order): RedirectResponse
    {
        abort_unless($order->employer_id === auth('employer')->id(), 404);

        return redirect()
            ->route('employer.plans.checkout', $order->plan_id)
            ->with('error', 'PayPal payment was cancelled.');
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    protected function complete(Order $order, PlanPurchaseService $purchases, array $payload): RedirectResponse
    {
        abort_unless($order->employer_id === auth('employer')->id(), 404);

        try {
            $order = $purchases->completeGatewayPayment($order, $payload);

            return redirect()
                ->route('employer.orders.show', $order)
                ->with('success', 'Payment successful. Your plan is now active.');
        } catch (Throwable $e) {
            report($e);

            return redirect()
                ->route('employer.plans.checkout', $order->plan_id)
                ->with('error', $e instanceof RuntimeException
                    ? $e->getMessage()
                    : 'Payment could not be verified. Please contact support if amount was deducted.');
        }
    }
}
