<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'subscription_id',
        'user_id',
        'transaction_type',
        'amount',
        'original_amount',
        'discount_amount',
        'discount_code',
        'currency',
        'payment_method',
        'payment_gateway',
        'transaction_reference',
        'gateway_reference',
        'gateway_response',
        'authorization_code',
        'status',
        'failure_reason',
        'retry_count',
        'last_retry_at',
        'paid_at',
        'refunded_at',
        'invoice_number',
        'receipt_url',
        'invoice_pdf_path',
        'customer_name',
        'customer_email',
        'customer_phone',
        'processed_by',
        'admin_notes',
        'is_auto_payment',
        'metadata'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'original_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'paid_at' => 'datetime',
        'refunded_at' => 'datetime',
        'last_retry_at' => 'datetime',
        'is_auto_payment' => 'boolean',
        'metadata' => 'array',
    ];

    // Relationships
    public function subscription(): BelongsTo
    {
        return $this->belongsTo(SystemSubscription::class, 'subscription_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function processor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function systemNotification(): BelongsTo
    {
        return $this->belongsTo(SystemNotification::class, 'related_transaction_id');
    }

    // Scopes
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    public function scopeRenewals($query)
    {
        return $query->where('transaction_type', 'subscription_renewal');
    }

    public function scopeAutoPayments($query)
    {
        return $query->where('is_auto_payment', true);
    }

    public function scopeRecent($query, $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    // Accessors
    public function getStatusBadgeColorAttribute(): string
    {
        return match($this->status) {
            'completed' => 'success',
            'pending', 'processing' => 'warning',
            'failed', 'cancelled' => 'danger',
            'refunded' => 'info',
            'disputed' => 'dark',
            default => 'secondary'
        };
    }

    public function getFormattedAmountAttribute(): string
    {
        return $this->currency . ' ' . number_format($this->amount, 2);
    }

    public function getPaymentMethodDisplayAttribute(): string
    {
        return match($this->payment_method) {
            'mobile_money_mtn' => 'MTN Mobile Money',
            'mobile_money_vodafone' => 'Vodafone Cash',
            'mobile_money_airteltigo' => 'AirtelTigo Money',
            'bank_transfer' => 'Bank Transfer',
            'card' => 'Card Payment',
            'cash' => 'Cash',
            default => ucfirst($this->payment_method ?? 'Unknown')
        };
    }

    // Business Logic Methods
    public function markAsCompleted(array $gatewayResponse = []): void
    {
        $this->update([
            'status' => 'completed',
            'paid_at' => now(),
            'gateway_response' => json_encode($gatewayResponse),
        ]);

        // Update subscription
        if ($this->subscription) {
            // Only call renew() for actual renewals (when subscription already has a past end date)
            // For new subscriptions, just update payment date and ensure status is active
            if ($this->subscription->subscription_end_date->isPast()) {
                // This is a renewal - extend the subscription
                $this->subscription->renew();
            } else {
                // This is a new subscription - just update payment info, don't change dates
                $this->subscription->last_payment_date = now();
                $this->subscription->status = 'active';
                $this->subscription->failed_payment_attempts = 0;
                $this->subscription->save();
            }
        }
    }

    public function markAsFailed(string $reason, array $gatewayResponse = []): void
    {
        $this->update([
            'status' => 'failed',
            'failure_reason' => $reason,
            'gateway_response' => json_encode($gatewayResponse),
        ]);

        // Update subscription failed attempts
        if ($this->subscription) {
            $this->subscription->incrementFailedPaymentAttempts();
        }
    }

    public function retry(): void
    {
        $this->increment('retry_count');
        $this->update([
            'status' => 'pending',
            'last_retry_at' => now(),
        ]);
    }

    public function refund(string $reason = null): void
    {
        $this->update([
            'status' => 'refunded',
            'refunded_at' => now(),
            'admin_notes' => ($this->admin_notes ? $this->admin_notes . "\n" : '') . 
                            "Refunded on " . now() . ($reason ? ": " . $reason : ''),
        ]);
    }

    public function generateInvoiceNumber(): string
    {
        if ($this->invoice_number) {
            return $this->invoice_number;
        }

        $prefix = 'INV';
        $year = now()->format('Y');
        $month = now()->format('m');
        $number = str_pad($this->id, 6, '0', STR_PAD_LEFT);
        
        $invoiceNumber = "{$prefix}-{$year}{$month}-{$number}";
        
        $this->update(['invoice_number' => $invoiceNumber]);
        
        return $invoiceNumber;
    }

    public function canRetry(): bool
    {
        return $this->status === 'failed' && $this->retry_count < 3;
    }

    public function canRefund(): bool
    {
        return $this->status === 'completed' && 
               $this->paid_at &&
               $this->paid_at->diffInDays(now()) <= 30;
    }
}

