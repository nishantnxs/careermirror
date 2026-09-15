<?php

namespace App\Services;

use App\Enums\ActivityAction;
use App\Enums\PaymentMode;
use App\Enums\PaymentStatus;
use App\Enums\SubscriptionStatus;
use App\Models\Admin;
use App\Models\Employer;
use App\Models\EmployerSubscription;
use App\Models\Order;
use App\Models\Plan;
use App\Payments\PaymentGatewayManager;
use App\Support\PaymentCheckout;
use App\Support\PaymentResult;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;
use RuntimeException;

class PlanPurchaseService
{
    public function __construct(
        public PaymentGatewayManager $gateways,
        public ActivityLogger $activity,
    ) {}

    /**
     * Activate a free plan immediately.
     *
     * @param  array{payment_notes?: string|null}  $payment
     */
    public function activateFreePlan(Employer $employer, Plan $plan, array $payment = []): Order
    {
        if (! $plan->isFree()) {
            throw new InvalidArgumentException('Only free plans can be activated without a payment gateway.');
        }

        if (! $plan->is_active || $plan->is_expired) {
            throw new InvalidArgumentException('This plan is not available for purchase.');
        }

        return $this->createPaidOrder($employer, $plan, [
            'final_amount' => 0,
            'payment_mode' => PaymentMode::Free,
            'transaction_reference' => null,
            'payment_notes' => $payment['payment_notes'] ?? 'Free plan activation',
            'payment_meta' => [],
        ]);
    }

    /**
     * Create a pending order and start gateway checkout for a paid plan.
     *
     * @return array{order: Order, checkout: PaymentCheckout}
     */
    public function beginGatewayCheckout(Employer $employer, Plan $plan, PaymentMode $mode): array
    {
        if ($plan->isFree()) {
            throw new InvalidArgumentException('Free plans do not require a payment gateway.');
        }

        if (! $mode->isGateway()) {
            throw new InvalidArgumentException('Select PayPal, Stripe, Razorpay, or UPI.');
        }

        if (! $plan->is_active || $plan->is_expired) {
            throw new InvalidArgumentException('This plan is not available for purchase.');
        }

        $order = $this->createPendingOrder($employer, $plan, $mode);
        $checkout = $this->gateways->driver($mode)->createCheckout($order->fresh(['employer']), $mode);

        return [
            'order' => $order->fresh(['employer', 'plan']),
            'checkout' => $checkout,
        ];
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function completeGatewayPayment(Order $order, array $payload = []): Order
    {
        if ($order->payment_status === PaymentStatus::Paid) {
            return $order->fresh(['plan', 'subscription']);
        }

        if ($order->payment_status !== PaymentStatus::Pending) {
            throw new RuntimeException('This order cannot be completed.');
        }

        $mode = $order->payment_mode;

        if ($mode === null || ! $mode->isGateway()) {
            throw new RuntimeException('Order is missing a payment gateway.');
        }

        $result = $this->gateways->driver($mode)->verify($order, $payload);

        if (! $result->successful) {
            $previousStatus = $order->payment_status;

            $order->update([
                'payment_status' => PaymentStatus::Failed,
                'payment_meta' => array_merge($order->payment_meta ?? [], [
                    'failure_message' => $result->message,
                    ...$result->meta,
                ]),
            ]);

            $this->activity->record(
                ActivityAction::PaymentUpdated,
                $order->fresh(),
                ['payment_status' => $previousStatus],
                [
                    'payment_status' => PaymentStatus::Failed,
                    'failure_message' => $result->message,
                ],
                "Payment failed for order {$order->order_number}.",
            );

            throw new RuntimeException($result->message ?: 'Payment verification failed.');
        }

        return $this->markOrderPaid($order, $result);
    }

    /**
     * Admin grants plan access to an employer, with an explicit payment amount audit trail.
     *
     * @param  array{
     *     payment_amount: float|int|string,
     *     payment_mode?: string|null,
     *     transaction_reference?: string|null,
     *     payment_notes?: string|null
     * }  $assignment
     */
    public function assignByAdmin(Admin $admin, Employer $employer, Plan $plan, array $assignment): Order
    {
        $finalAmount = (float) $assignment['payment_amount'];
        $paymentMode = PaymentMode::from(
            $assignment['payment_mode'] ?? PaymentMode::AdminAssigned->value
        );

        if ($finalAmount <= 0) {
            $paymentMode = PaymentMode::Free;
        }

        return $this->createPaidOrder($employer, $plan, [
            'final_amount' => max(0, $finalAmount),
            'payment_mode' => $paymentMode,
            'transaction_reference' => $assignment['transaction_reference'] ?? null,
            'payment_notes' => $assignment['payment_notes'] ?? 'Assigned by admin',
            'payment_meta' => [
                'assigned_by_admin' => true,
                'assigned_by_admin_id' => $admin->id,
                'assigned_by_admin_name' => $admin->name,
                'assigned_by_admin_email' => $admin->email,
                'catalog_payable_amount' => $plan->payable_amount,
            ],
            'activity_action' => ActivityAction::EmployerPlanAssigned,
            'activity_description' => sprintf(
                'Plan "%s" assigned to employer "%s".',
                $plan->title,
                $employer->company_name,
            ),
        ]);
    }

    protected function createPendingOrder(Employer $employer, Plan $plan, PaymentMode $mode): Order
    {
        $finalAmount = $plan->payable_amount;
        $discountAmount = max(0, (float) $plan->amount - $finalAmount);

        return Order::create([
            'order_number' => $this->generateOrderNumber(),
            'employer_id' => $employer->id,
            'plan_id' => $plan->id,
            'plan_title' => $plan->title,
            'currency' => $plan->currency,
            'amount' => $plan->amount,
            'discount_amount' => $discountAmount,
            'final_amount' => $finalAmount,
            'payment_status' => PaymentStatus::Pending,
            'payment_mode' => $mode,
            'transaction_reference' => null,
            'paid_at' => null,
            'payment_notes' => null,
            'payment_meta' => array_filter([
                'plan_type' => $plan->plan_type->value,
                'duration_label' => $plan->duration_label,
                'job_duration_label' => $plan->job_duration_label,
                'recorded_ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'gateway' => $mode->value,
            ], fn ($value) => $value !== null && $value !== ''),
            'jobs_allowed' => $plan->jobs_allowed,
            'plan_duration_days' => $plan->duration_in_days,
            'job_duration_days' => $plan->job_duration_in_days,
        ]);
    }

    /**
     * @param  array{
     *     final_amount: float,
     *     payment_mode: PaymentMode,
     *     transaction_reference?: string|null,
     *     payment_notes?: string|null,
     *     payment_meta?: array<string, mixed>,
     *     activity_action?: ActivityAction|null,
     *     activity_description?: string|null
     * }  $details
     */
    protected function createPaidOrder(Employer $employer, Plan $plan, array $details): Order
    {
        return DB::transaction(function () use ($employer, $plan, $details) {
            $finalAmount = (float) $details['final_amount'];
            $discountAmount = max(0, (float) $plan->amount - $finalAmount);

            $order = Order::create([
                'order_number' => $this->generateOrderNumber(),
                'employer_id' => $employer->id,
                'plan_id' => $plan->id,
                'plan_title' => $plan->title,
                'currency' => $plan->currency,
                'amount' => $plan->amount,
                'discount_amount' => $discountAmount,
                'final_amount' => $finalAmount,
                'payment_status' => PaymentStatus::Paid,
                'payment_mode' => $details['payment_mode'],
                'transaction_reference' => $details['transaction_reference'] ?? null,
                'paid_at' => now(),
                'payment_notes' => $details['payment_notes'] ?? null,
                'payment_meta' => array_filter([
                    'plan_type' => $plan->plan_type->value,
                    'duration_label' => $plan->duration_label,
                    'job_duration_label' => $plan->job_duration_label,
                    'recorded_ip' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                    ...($details['payment_meta'] ?? []),
                ], fn ($value) => $value !== null && $value !== ''),
                'jobs_allowed' => $plan->jobs_allowed,
                'plan_duration_days' => $plan->duration_in_days,
                'job_duration_days' => $plan->job_duration_in_days,
            ]);

            $this->activateSubscription($employer, $order, $plan);

            $order = $order->fresh(['plan', 'subscription']);

            $action = $details['activity_action'] ?? ActivityAction::PlanPurchased;

            $this->activity->record(
                $action,
                $order,
                null,
                [
                    'order_number' => $order->order_number,
                    'plan_title' => $order->plan_title,
                    'final_amount' => $order->final_amount,
                    'payment_status' => $order->payment_status,
                    'payment_mode' => $order->payment_mode,
                    'employer_id' => $order->employer_id,
                ],
                $details['activity_description'] ?? "Plan \"{$plan->title}\" purchased.",
            );

            if ($discountAmount > 0) {
                $this->activity->record(
                    ActivityAction::DiscountApplied,
                    $order,
                    ['amount' => $order->amount],
                    [
                        'discount_amount' => $order->discount_amount,
                        'final_amount' => $order->final_amount,
                    ],
                    "Discount applied on order {$order->order_number}.",
                );
            }

            $this->activity->record(
                ActivityAction::PaymentUpdated,
                $order,
                ['payment_status' => PaymentStatus::Pending->value],
                [
                    'payment_status' => $order->payment_status,
                    'transaction_reference' => $order->transaction_reference,
                    'paid_at' => $order->paid_at,
                ],
                "Payment recorded for order {$order->order_number}.",
            );

            return $order;
        });
    }

    protected function markOrderPaid(Order $order, PaymentResult $result): Order
    {
        return DB::transaction(function () use ($order, $result) {
            $order = Order::query()->whereKey($order->id)->lockForUpdate()->firstOrFail();

            if ($order->payment_status === PaymentStatus::Paid) {
                return $order->fresh(['plan', 'subscription']);
            }

            $previousStatus = $order->payment_status;

            $order->update([
                'payment_status' => PaymentStatus::Paid,
                'transaction_reference' => $result->transactionReference,
                'paid_at' => now(),
                'payment_notes' => 'Paid via '.($order->payment_mode?->label() ?? 'gateway'),
                'payment_meta' => array_merge($order->payment_meta ?? [], $result->meta, [
                    'verified_at' => now()->toIso8601String(),
                ]),
            ]);

            $this->activateSubscription($order->employer, $order, $order->plan);

            $order = $order->fresh(['plan', 'subscription']);

            $this->activity->record(
                ActivityAction::PaymentUpdated,
                $order,
                ['payment_status' => $previousStatus],
                [
                    'payment_status' => $order->payment_status,
                    'transaction_reference' => $order->transaction_reference,
                    'paid_at' => $order->paid_at,
                ],
                "Payment updated for order {$order->order_number}.",
            );

            $this->activity->record(
                ActivityAction::PlanPurchased,
                $order,
                null,
                [
                    'order_number' => $order->order_number,
                    'plan_title' => $order->plan_title,
                    'final_amount' => $order->final_amount,
                    'payment_mode' => $order->payment_mode,
                ],
                "Plan \"{$order->plan_title}\" purchased.",
            );

            return $order;
        });
    }

    protected function activateSubscription(Employer $employer, Order $order, Plan $plan): EmployerSubscription
    {
        $previous = EmployerSubscription::query()
            ->where('employer_id', $employer->id)
            ->active()
            ->lockForUpdate()
            ->get();

        $carriedJobCredits = $previous->sum(
            fn (EmployerSubscription $subscription) => $subscription->remainingJobs()
        );

        if ($previous->isNotEmpty()) {
            EmployerSubscription::query()
                ->whereIn('id', $previous->modelKeys())
                ->update(['status' => SubscriptionStatus::Cancelled]);
        }

        $startsAt = now();
        $endsAt = $plan->duration_in_days === null
            ? null
            : $startsAt->copy()->addDays($plan->duration_in_days);

        return EmployerSubscription::create([
            'employer_id' => $employer->id,
            'order_id' => $order->id,
            'plan_id' => $plan->id,
            'status' => SubscriptionStatus::Active,
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
            'jobs_allowed' => $plan->jobs_allowed + $carriedJobCredits,
            'jobs_used' => 0,
            'job_duration_days' => $plan->job_duration_in_days,
        ]);
    }

    protected function generateOrderNumber(): string
    {
        do {
            $number = 'ORD-'.now()->format('Ymd').'-'.Str::upper(Str::random(6));
        } while (Order::query()->where('order_number', $number)->exists());

        return $number;
    }
}
