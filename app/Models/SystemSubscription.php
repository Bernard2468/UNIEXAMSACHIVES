<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class SystemSubscription extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'institution_name',
        'institution_code',
        'subscription_plan',
        'subscription_start_date',
        'subscription_end_date',
        'renewal_cycle',
        'renewal_amount',
        'currency',
        'hosting_package_type',
        'package_features',
        'status',
        'auto_renewal',
        'grace_period_days',
        'payment_gateway_subscription_id',
        'payment_gateway_customer_id',
        'last_payment_date',
        'next_payment_date',
        'failed_payment_attempts',
        'last_failed_payment_at',
        'renewal_reminder_30_days_sent',
        'renewal_reminder_14_days_sent',
        'renewal_reminder_7_days_sent',
        'renewal_reminder_1_day_sent',
        'expiry_notification_sent',
        'created_by',
        'updated_by',
        'admin_notes',
        'metadata'
    ];

    protected $casts = [
        'subscription_start_date' => 'date',
        'subscription_end_date' => 'date',
        'last_payment_date' => 'datetime',
        'next_payment_date' => 'datetime',
        'last_failed_payment_at' => 'datetime',
        'auto_renewal' => 'boolean',
        'renewal_reminder_30_days_sent' => 'boolean',
        'renewal_reminder_14_days_sent' => 'boolean',
        'renewal_reminder_7_days_sent' => 'boolean',
        'renewal_reminder_1_day_sent' => 'boolean',
        'expiry_notification_sent' => 'boolean',
        'metadata' => 'array',
        'renewal_amount' => 'decimal:2',
    ];

    // Relationships
    public function transactions(): HasMany
    {
        return $this->hasMany(PaymentTransaction::class, 'subscription_id');
    }

    public function completedTransactions(): HasMany
    {
        return $this->transactions()->where('status', 'completed');
    }

    public function pendingTransactions(): HasMany
    {
        return $this->transactions()->where('status', 'pending');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function systemNotifications(): HasMany
    {
        return $this->hasMany(SystemNotification::class, 'subscription_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeExpiringSoon($query, $days = 30)
    {
        return $query->where('status', 'active')
                     ->whereDate('subscription_end_date', '<=', now()->addDays($days))
                     ->whereDate('subscription_end_date', '>=', now());
    }

    public function scopeExpired($query)
    {
        return $query->where('status', 'expired')
                     ->orWhere(function($q) {
                         $q->where('status', 'active')
                           ->whereDate('subscription_end_date', '<', now());
                     });
    }

    public function scopeWithAutoRenewal($query)
    {
        return $query->where('auto_renewal', true);
    }

    public function scopeInGracePeriod($query)
    {
        return $query->where('status', 'expired')
                     ->whereRaw('DATE_ADD(subscription_end_date, INTERVAL grace_period_days DAY) >= CURDATE()');
    }

    // Accessors & Mutators
    public function getDaysUntilExpiryAttribute(): int
    {
        return now()->diffInDays($this->subscription_end_date, false);
    }

    public function getIsExpiringSoonAttribute(): bool
    {
        return $this->days_until_expiry <= 30 && $this->days_until_expiry >= 0;
    }

    public function getIsExpiredAttribute(): bool
    {
        return $this->days_until_expiry < 0;
    }

    public function getIsInGracePeriodAttribute(): bool
    {
        if (!$this->is_expired) {
            return false;
        }
        
        $gracePeriodEnd = $this->subscription_end_date->addDays($this->grace_period_days);
        return now()->lte($gracePeriodEnd);
    }

    public function getGracePeriodEndsAtAttribute(): ?Carbon
    {
        if (!$this->is_expired) {
            return null;
        }
        
        return $this->subscription_end_date->copy()->addDays($this->grace_period_days);
    }

    public function getStatusBadgeColorAttribute(): string
    {
        return match($this->status) {
            'active' => 'success',
            'expiring_soon' => 'warning',
            'expired' => 'danger',
            'suspended' => 'secondary',
            'cancelled' => 'dark',
            default => 'info'
        };
    }

    public function getFormattedRenewalAmountAttribute(): string
    {
        return $this->currency . ' ' . number_format($this->renewal_amount, 2);
    }

    // Business Logic Methods
    public function updateStatus(): void
    {
        $daysUntilExpiry = $this->days_until_expiry;

        if ($daysUntilExpiry < -$this->grace_period_days) {
            $this->status = 'suspended';
        } elseif ($daysUntilExpiry < 0) {
            $this->status = 'expired';
        } elseif ($daysUntilExpiry <= 30) {
            $this->status = 'expiring_soon';
        } else {
            $this->status = 'active';
        }

        $this->save();
    }

    public function renew($months = null): void
    {
        if (!$months) {
            $months = $this->getRenewalMonths();
        }

        $startDate = $this->subscription_end_date->isFuture() 
            ? $this->subscription_end_date 
            : now();

        $this->subscription_start_date = $startDate;
        $this->subscription_end_date = $startDate->copy()->addMonths($months);
        $this->status = 'active';
        $this->failed_payment_attempts = 0;
        $this->last_payment_date = now();
        $this->next_payment_date = $this->subscription_end_date->copy()->subDays(7);
        
        // Reset reminder flags
        $this->resetReminderFlags();
        
        $this->save();
    }

    public function suspend(string $reason = null): void
    {
        $this->status = 'suspended';
        if ($reason) {
            $this->admin_notes = ($this->admin_notes ? $this->admin_notes . "\n" : '') . 
                                 "Suspended on " . now() . ": " . $reason;
        }
        $this->save();
    }

    public function reactivate(): void
    {
        $this->status = 'active';
        $this->failed_payment_attempts = 0;
        $this->save();
    }

    public function incrementFailedPaymentAttempts(): void
    {
        $this->failed_payment_attempts++;
        $this->last_failed_payment_at = now();
        
        if ($this->failed_payment_attempts >= 3) {
            $this->auto_renewal = false;
        }
        
        $this->save();
    }

    public function resetReminderFlags(): void
    {
        $this->update([
            'renewal_reminder_30_days_sent' => false,
            'renewal_reminder_14_days_sent' => false,
            'renewal_reminder_7_days_sent' => false,
            'renewal_reminder_1_day_sent' => false,
            'expiry_notification_sent' => false,
        ]);
    }

    private function getRenewalMonths(): int
    {
        return match($this->renewal_cycle) {
            'monthly' => 1,
            'quarterly' => 3,
            'semi_annual' => 6,
            'annual' => 12,
            default => 12
        };
    }

    public function getTotalRevenue(): float
    {
        return $this->completedTransactions()
                    ->sum('amount');
    }

    public function getLastSuccessfulPayment(): ?PaymentTransaction
    {
        return $this->completedTransactions()
                    ->latest()
                    ->first();
    }
}

