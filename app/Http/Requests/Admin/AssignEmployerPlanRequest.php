<?php

namespace App\Http\Requests\Admin;

use App\Enums\PaymentMode;
use App\Models\Plan;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AssignEmployerPlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('admin')->check();
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'plan_id' => ['required', 'integer', Rule::exists('plans', 'id')->whereNull('deleted_at')],
            'payment_amount' => ['required', 'numeric', 'min:0', 'max:9999999.99'],
            'payment_mode' => ['required', Rule::enum(PaymentMode::class)->except([PaymentMode::Free])],
            'transaction_reference' => [
                Rule::requiredIf(function () {
                    $mode = PaymentMode::tryFrom((string) $this->input('payment_mode'));

                    return $mode?->requiresReference() ?? false;
                }),
                'nullable',
                'string',
                'max:191',
            ],
            'payment_notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'plan_id.required' => 'Please select a plan to assign.',
            'payment_amount.required' => 'Please enter the payment amount collected.',
            'payment_mode.required' => 'Please select a payment mode.',
            'transaction_reference.required' => 'Please enter the transaction / UTR / cheque reference.',
        ];
    }

    public function plan(): Plan
    {
        return Plan::query()->findOrFail($this->integer('plan_id'));
    }

    /**
     * @return array{
     *     payment_amount: float,
     *     payment_mode: string,
     *     transaction_reference: string|null,
     *     payment_notes: string|null
     * }
     */
    public function assignmentData(): array
    {
        return [
            'payment_amount' => (float) $this->input('payment_amount'),
            'payment_mode' => $this->input('payment_mode'),
            'transaction_reference' => $this->filled('transaction_reference')
                ? $this->string('transaction_reference')->trim()->value()
                : null,
            'payment_notes' => $this->filled('payment_notes')
                ? $this->string('payment_notes')->trim()->value()
                : null,
        ];
    }
}
