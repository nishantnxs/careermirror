<?php

namespace App\Models;

use App\Enums\PaymentMode;
use App\Enums\PaymentStatus;
use Database\Factories\OrderFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    /** @use HasFactory<OrderFactory> */
    use HasFactory;

    protected $fillable = [
        'order_number',
        'employer_id',
        'plan_id',
        'plan_title',
        'currency',
        'amount',
        'discount_amount',
        'final_amount',
        'payment_status',
        'payment_mode',
        'transaction_reference',
        'paid_at',
        'payment_notes',
        'payment_meta',
        'jobs_allowed',
        'plan_duration_days',
        'job_duration_days',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'final_amount' => 'decimal:2',
            'payment_status' => PaymentStatus::class,
            'payment_mode' => PaymentMode::class,
            'paid_at' => 'datetime',
            'payment_meta' => 'array',
            'jobs_allowed' => 'integer',
            'plan_duration_days' => 'integer',
            'job_duration_days' => 'integer',
        ];
    }

    public function employer(): BelongsTo
    {
        return $this->belongsTo(Employer::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function subscription(): HasOne
    {
        return $this->hasOne(EmployerSubscription::class);
    }

    public function getFormattedFinalAmountAttribute(): string
    {
        if ((float) $this->final_amount <= 0) {
            return 'Free';
        }

        return $this->currencySymbol().number_format((float) $this->final_amount, 2);
    }

    public function currencySymbol(): string
    {
        return match ($this->currency) {
            'INR' => '₹',
            'USD' => '$',
            'EUR' => '€',
            'GBP' => '£',
            default => $this->currency.' ',
        };
    }
}
