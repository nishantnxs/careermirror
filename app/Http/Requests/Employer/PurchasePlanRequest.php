<?php

namespace App\Http\Requests\Employer;

use App\Enums\PaymentMode;
use App\Models\Plan;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class PurchasePlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('employer')->check();
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        /** @var Plan $plan */
        $plan = $this->route('plan');

        if ($plan->isFree()) {
            return [
                'payment_notes' => ['nullable', 'string', 'max:1000'],
            ];
        }

        return [
            'payment_mode' => ['required', Rule::in(array_keys(PaymentMode::gatewayOptions()))],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            /** @var Plan $plan */
            $plan = $this->route('plan');

            if (! $plan->is_active || $plan->is_expired) {
                $validator->errors()->add('plan', 'This plan is no longer available.');
            }
        });
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'payment_mode.required' => 'Please choose a payment method: PayPal, Stripe, Razorpay, or UPI.',
            'payment_mode.in' => 'Please choose a payment method: PayPal, Stripe, Razorpay, or UPI.',
        ];
    }

    public function paymentMode(): ?PaymentMode
    {
        if (! $this->filled('payment_mode')) {
            return null;
        }

        return PaymentMode::from($this->string('payment_mode')->toString());
    }

    /**
     * @return array{payment_notes?: string|null}
     */
    public function paymentData(): array
    {
        return [
            'payment_notes' => $this->filled('payment_notes')
                ? $this->string('payment_notes')->trim()->value()
                : null,
        ];
    }
}
