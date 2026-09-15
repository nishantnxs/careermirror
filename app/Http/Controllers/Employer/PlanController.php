<?php

namespace App\Http\Controllers\Employer;

use App\Enums\PaymentMode;
use App\Http\Controllers\Controller;
use App\Http\Requests\Employer\PurchasePlanRequest;
use App\Models\Plan;
use App\Services\PlanPurchaseService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use RuntimeException;
use Throwable;

class PlanController extends Controller
{
    public function index(): View
    {
        $plans = Plan::query()
            ->available()
            ->ordered()
            ->get();

        $subscription = auth('employer')->user()->currentSubscription()?->load('plan');

        return view('employer.plans.index', compact('plans', 'subscription'));
    }

    public function checkout(Plan $plan): View|RedirectResponse
    {
        if (! $plan->is_active || $plan->is_expired) {
            return redirect()
                ->route('employer.plans.index')
                ->with('error', 'This plan is no longer available.');
        }

        return view('employer.plans.checkout', [
            'plan' => $plan,
            'gatewayOptions' => PaymentMode::gatewayOptions(),
        ]);
    }

    public function purchase(PurchasePlanRequest $request, Plan $plan, PlanPurchaseService $purchases): RedirectResponse|View
    {
        if (! $plan->is_active || $plan->is_expired) {
            return redirect()
                ->route('employer.plans.index')
                ->with('error', 'This plan is no longer available.');
        }

        try {
            if ($plan->isFree()) {
                $order = $purchases->activateFreePlan(
                    auth('employer')->user(),
                    $plan,
                    $request->paymentData(),
                );

                return redirect()
                    ->route('employer.orders.show', $order)
                    ->with('success', "Free plan \"{$plan->title}\" activated successfully.");
            }

            $result = $purchases->beginGatewayCheckout(
                auth('employer')->user(),
                $plan,
                $request->paymentMode(),
            );

            $checkout = $result['checkout'];
            $order = $result['order'];

            if ($checkout->type === 'redirect' && filled($checkout->redirectUrl)) {
                return redirect()->away($checkout->redirectUrl);
            }

            return view('employer.plans.pay', [
                'plan' => $plan,
                'order' => $order,
                'checkout' => $checkout,
            ]);
        } catch (Throwable $e) {
            report($e);

            return redirect()
                ->route('employer.plans.checkout', $plan)
                ->with('error', $e instanceof RuntimeException
                    ? $e->getMessage()
                    : 'Unable to start payment. Please try again.');
        }
    }
}
