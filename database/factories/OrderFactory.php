<?php

namespace Database\Factories;

use App\Enums\PaymentMode;
use App\Enums\PaymentStatus;
use App\Models\Employer;
use App\Models\Order;
use App\Models\Plan;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        $amount = fake()->randomFloat(2, 199, 4999);

        return [
            'order_number' => 'ORD-'.now()->format('Ymd').'-'.Str::upper(Str::random(6)),
            'employer_id' => Employer::factory(),
            'plan_id' => Plan::factory(),
            'plan_title' => fake()->words(2, true),
            'currency' => 'INR',
            'amount' => $amount,
            'discount_amount' => 0,
            'final_amount' => $amount,
            'payment_status' => PaymentStatus::Paid,
            'payment_mode' => PaymentMode::Upi,
            'transaction_reference' => 'TXN'.fake()->numerify('########'),
            'paid_at' => now(),
            'payment_notes' => null,
            'payment_meta' => null,
            'jobs_allowed' => 5,
            'plan_duration_days' => 30,
            'job_duration_days' => 30,
        ];
    }

    public function free(): static
    {
        return $this->state(fn () => [
            'amount' => 0,
            'discount_amount' => 0,
            'final_amount' => 0,
            'payment_mode' => PaymentMode::Free,
            'transaction_reference' => null,
            'payment_notes' => 'Free plan activation',
        ]);
    }
}
