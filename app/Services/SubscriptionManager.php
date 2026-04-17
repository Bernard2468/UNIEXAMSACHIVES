<?php

namespace App\Services;

use App\Models\SystemSubscription;
use App\Models\PaymentTransaction;
use App\Models\SystemNotification;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Exception;

class SubscriptionManager
{
    private PaystackService $paystack;
    private SystemNotificationService $notificationService;

    public function __construct()
    {
        $this->paystack = new PaystackService();
        $this->notificationService = new SystemNotificationService();
    }

    /**
     * Create a new subscription
     */
    public function createSubscription(array $data): SystemSubscription
    {
        $subscription = SystemSubscription::create([
            'institution_name' => $data['institution_name'],
            'institution_code' => $data['institution_code'] ?? Str::slug($data['institution_name']),
            'subscription_plan' => $data['subscription_plan'] ?? 'standard',
            'subscription_start_date' => $data['subscription_start_date'] ?? now(),
            'subscription_end_date' => $data['subscription_end_date'],
            'renewal_cycle' => $data['renewal_cycle'] ?? 'annual',
            'renewal_amount' => $data['renewal_amount'],
            'currency' => $data['currency'] ?? 'GHS',
            'hosting_package_type' => $data['hosting_package_type'] ?? null,
            'package_features' => $data['package_features'] ?? null,
            'status' => 'active',
            'auto_renewal' => $data['auto_renewal'] ?? true,
            'grace_period_days' => $data['grace_period_days'] ?? 7,
            'created_by' => $data['created_by'] ?? auth()->id(),
            'admin_notes' => $data['admin_notes'] ?? null,
            'metadata' => $data['metadata'] ?? null,
        ]);

        // Calculate next payment date
        $subscription->next_payment_date = $subscription->subscription_end_date->copy()->subDays(7);
        $subscription->save();

        Log::info('Subscription created', ['subscription_id' => $subscription->id]);

        return $subscription;
    }

    /**
     * Create a payment transaction for subscription renewal
     */
    public function createRenewalTransaction(SystemSubscription $subscription, ?int $userId = null): PaymentTransaction
    {
        // Get admin users for the institution
        $adminUser = User::where('role', 'admin')->first();
        
        $transaction = PaymentTransaction::create([
            'subscription_id' => $subscription->id,
            'user_id' => $userId ?? $adminUser->id ?? null,
            'transaction_type' => 'subscription_renewal',
            'amount' => $subscription->renewal_amount,
            'original_amount' => $subscription->renewal_amount,
            'currency' => $subscription->currency,
            'payment_gateway' => 'paystack',
            'transaction_reference' => $this->generateTransactionReference(),
            'status' => 'pending',
            'customer_name' => $subscription->institution_name,
            'customer_email' => $adminUser->email ?? 'admin@institution.edu',
            'customer_phone' => $adminUser->phone ?? null,
            'is_auto_payment' => false,
        ]);

        Log::info('Renewal transaction created', [
            'transaction_id' => $transaction->id,
            'subscription_id' => $subscription->id
        ]);

        return $transaction;
    }

    /**
     * Initiate manual payment for subscription renewal
     */
    public function initiateManualRenewal(SystemSubscription $subscription, int $userId, string $callbackUrl): array
    {
        try {
            // Create transaction
            $transaction = $this->createRenewalTransaction($subscription, $userId);

            // Initialize payment with Paystack
            $result = $this->paystack->initializePayment($transaction, $callbackUrl);

            if ($result['success']) {
                return [
                    'success' => true,
                    'transaction_id' => $transaction->id,
                    'payment_url' => $result['authorization_url'],
                    'reference' => $result['reference'],
                ];
            }

            return [
                'success' => false,
                'message' => $result['message'] ?? 'Payment initialization failed',
            ];

        } catch (Exception $e) {
            Log::error('Manual renewal initiation error', [
                'subscription_id' => $subscription->id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Failed to initiate payment: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Process automatic renewal using saved authorization
     */
    public function processAutoRenewal(SystemSubscription $subscription): bool
    {
        if (!$subscription->auto_renewal) {
            Log::info('Auto-renewal disabled for subscription', ['subscription_id' => $subscription->id]);
            return false;
        }

        try {
            // Get last successful payment's authorization code
            $lastPayment = $subscription->getLastSuccessfulPayment();

            if (!$lastPayment || !$lastPayment->authorization_code) {
                Log::warning('No authorization code found for auto-renewal', [
                    'subscription_id' => $subscription->id
                ]);
                
                // Send notification to pay manually
                $this->notificationService->sendAutoRenewalFailedNotification($subscription, 'No saved payment method');
                
                return false;
            }

            // Create new transaction for auto-renewal
            $transaction = PaymentTransaction::create([
                'subscription_id' => $subscription->id,
                'transaction_type' => 'subscription_renewal',
                'amount' => $subscription->renewal_amount,
                'original_amount' => $subscription->renewal_amount,
                'currency' => $subscription->currency,
                'payment_gateway' => 'paystack',
                'transaction_reference' => $this->generateTransactionReference(),
                'authorization_code' => $lastPayment->authorization_code,
                'status' => 'processing',
                'customer_name' => $subscription->institution_name,
                'customer_email' => $lastPayment->customer_email,
                'is_auto_payment' => true,
            ]);

            // Charge authorization
            $result = $this->paystack->chargeAuthorization($transaction, $lastPayment->authorization_code);

            if ($result['success']) {
                Log::info('Auto-renewal successful', [
                    'subscription_id' => $subscription->id,
                    'transaction_id' => $transaction->id
                ]);

                // Send success notification
                $this->notificationService->sendRenewalSuccessNotification($subscription, $transaction);

                return true;
            }

            Log::warning('Auto-renewal failed', [
                'subscription_id' => $subscription->id,
                'message' => $result['message'] ?? 'Unknown error'
            ]);

            // Send failure notification
            $this->notificationService->sendAutoRenewalFailedNotification($subscription, $result['message'] ?? 'Payment failed');

            return false;

        } catch (Exception $e) {
            Log::error('Auto-renewal error', [
                'subscription_id' => $subscription->id,
                'error' => $e->getMessage()
            ]);

            $this->notificationService->sendAutoRenewalFailedNotification($subscription, $e->getMessage());

            return false;
        }
    }

    /**
     * Check and update subscription statuses
     */
    public function checkExpiringSubscriptions(): array
    {
        $results = [
            'checked' => 0,
            'expiring_soon' => 0,
            'expired' => 0,
            'suspended' => 0,
        ];

        $subscriptions = SystemSubscription::whereIn('status', ['active', 'expiring_soon', 'expired'])->get();

        foreach ($subscriptions as $subscription) {
            $results['checked']++;
            
            $oldStatus = $subscription->status;
            $subscription->updateStatus();
            
            if ($subscription->status !== $oldStatus) {
                Log::info('Subscription status updated', [
                    'subscription_id' => $subscription->id,
                    'old_status' => $oldStatus,
                    'new_status' => $subscription->status
                ]);

                match($subscription->status) {
                    'expiring_soon' => $results['expiring_soon']++,
                    'expired' => $results['expired']++,
                    'suspended' => $results['suspended']++,
                    default => null
                };
            }
        }

        return $results;
    }

    /**
     * Send renewal reminders
     */
    public function sendRenewalReminders(): array
    {
        $results = [
            'checked' => 0,
            'reminders_sent' => 0,
        ];

        $subscriptions = SystemSubscription::active()
            ->orWhere('status', 'expiring_soon')
            ->get();

        foreach ($subscriptions as $subscription) {
            $results['checked']++;
            
            $daysUntilExpiry = $subscription->days_until_expiry;

            // 30 days reminder
            if ($daysUntilExpiry <= 30 && $daysUntilExpiry > 14 && !$subscription->renewal_reminder_30_days_sent) {
                $this->notificationService->sendRenewalReminder($subscription, 30);
                $subscription->update(['renewal_reminder_30_days_sent' => true]);
                $results['reminders_sent']++;
            }

            // 14 days reminder
            if ($daysUntilExpiry <= 14 && $daysUntilExpiry > 7 && !$subscription->renewal_reminder_14_days_sent) {
                $this->notificationService->sendRenewalReminder($subscription, 14);
                $subscription->update(['renewal_reminder_14_days_sent' => true]);
                $results['reminders_sent']++;
            }

            // 7 days reminder
            if ($daysUntilExpiry <= 7 && $daysUntilExpiry > 1 && !$subscription->renewal_reminder_7_days_sent) {
                $this->notificationService->sendRenewalReminder($subscription, 7);
                $subscription->update(['renewal_reminder_7_days_sent' => true]);
                $results['reminders_sent']++;
            }

            // 1 day reminder
            if ($daysUntilExpiry <= 1 && $daysUntilExpiry >= 0 && !$subscription->renewal_reminder_1_day_sent) {
                $this->notificationService->sendRenewalReminder($subscription, 1);
                $subscription->update(['renewal_reminder_1_day_sent' => true]);
                $results['reminders_sent']++;
            }

            // Expiry notification
            if ($daysUntilExpiry < 0 && !$subscription->expiry_notification_sent) {
                $this->notificationService->sendExpiryNotification($subscription);
                $subscription->update(['expiry_notification_sent' => true]);
                $results['reminders_sent']++;
            }
        }

        return $results;
    }

    /**
     * Attempt auto-renewal for subscriptions
     */
    public function processAutoRenewals(): array
    {
        $results = [
            'checked' => 0,
            'attempted' => 0,
            'successful' => 0,
            'failed' => 0,
        ];

        // Get subscriptions that need auto-renewal (expiring in 3 days or already expired but in grace period)
        $subscriptions = SystemSubscription::withAutoRenewal()
            ->where(function($query) {
                $query->where('status', 'expiring_soon')
                      ->orWhere('status', 'expired');
            })
            ->whereDate('subscription_end_date', '>=', now()->subDays(7)) // Within grace period
            ->whereDate('subscription_end_date', '<=', now()->addDays(3)) // 3 days before expiry
            ->get();

        foreach ($subscriptions as $subscription) {
            $results['checked']++;

            // Skip if already tried today
            if ($subscription->last_failed_payment_at && $subscription->last_failed_payment_at->isToday()) {
                continue;
            }

            $results['attempted']++;

            if ($this->processAutoRenewal($subscription)) {
                $results['successful']++;
            } else {
                $results['failed']++;
            }
        }

        return $results;
    }

    /**
     * Suspend expired subscriptions beyond grace period
     */
    public function suspendExpiredSubscriptions(): array
    {
        $results = [
            'checked' => 0,
            'suspended' => 0,
        ];

        $subscriptions = SystemSubscription::where('status', 'expired')->get();

        foreach ($subscriptions as $subscription) {
            $results['checked']++;

            if (!$subscription->is_in_grace_period) {
                $subscription->suspend('Grace period expired');
                $this->notificationService->sendSuspensionNotification($subscription);
                $results['suspended']++;

                Log::info('Subscription suspended', ['subscription_id' => $subscription->id]);
            }
        }

        return $results;
    }

    /**
     * Verify payment and update subscription
     */
    public function verifyAndCompletePayment(string $reference): array
    {
        try {
            // Find transaction
            $transaction = PaymentTransaction::where('transaction_reference', $reference)
                ->orWhere('gateway_reference', $reference)
                ->first();

            if (!$transaction) {
                return [
                    'success' => false,
                    'message' => 'Transaction not found',
                ];
            }

            // Verify with Paystack
            $result = $this->paystack->verifyPayment($reference);

            // If API call was successful, check the actual payment status
            if ($result['success']) {
                $paymentData = $result['data'];
                $paymentStatus = $paymentData['status'] ?? null;

                // Payment was successful
                if ($paymentStatus === 'success') {
                    // Mark transaction as completed only if not already completed
                    if ($transaction->status !== 'completed') {
                        $transaction->markAsCompleted($paymentData);
                    }

                    return [
                        'success' => true,
                        'transaction' => $transaction,
                        'message' => 'Payment verified and subscription renewed successfully',
                    ];
                }

                // Payment was failed, cancelled, or not successful
                // Mark transaction as failed if it's still pending
                if ($transaction->status === 'pending' || $transaction->status === 'processing') {
                    $failureReason = $paymentData['gateway_response'] ?? 
                                    $paymentData['message'] ?? 
                                    'Payment was not completed';
                    
                    $transaction->markAsFailed($failureReason, $paymentData);

                    Log::info('Payment marked as failed', [
                        'transaction_id' => $transaction->id,
                        'reference' => $reference,
                        'payment_status' => $paymentStatus,
                        'reason' => $failureReason
                    ]);
                }

                return [
                    'success' => false,
                    'message' => 'Payment was not completed. Status: ' . ($paymentStatus ?? 'unknown'),
                ];
            }

            // API call failed - mark transaction as failed if still pending
            if ($transaction->status === 'pending' || $transaction->status === 'processing') {
                $failureReason = $result['message'] ?? 'Payment verification failed';
                $transaction->markAsFailed($failureReason);

                Log::info('Payment verification failed', [
                    'transaction_id' => $transaction->id,
                    'reference' => $reference,
                    'reason' => $failureReason
                ]);
            }

            return [
                'success' => false,
                'message' => $result['message'] ?? 'Payment verification failed',
            ];

        } catch (Exception $e) {
            Log::error('Payment verification error', [
                'reference' => $reference,
                'error' => $e->getMessage()
            ]);

            // Try to find and mark transaction as failed
            try {
                $transaction = PaymentTransaction::where('transaction_reference', $reference)
                    ->orWhere('gateway_reference', $reference)
                    ->first();

                if ($transaction && ($transaction->status === 'pending' || $transaction->status === 'processing')) {
                    $transaction->markAsFailed('Verification error: ' . $e->getMessage());
                }
            } catch (Exception $ex) {
                // Ignore errors when trying to mark as failed
            }

            return [
                'success' => false,
                'message' => 'Verification error: ' . $e->getMessage(),
            ];
        }
    }

    // ===== Private Helper Methods =====

    private function generateTransactionReference(): string
    {
        return 'TXN-' . strtoupper(Str::random(10)) . '-' . time();
    }
}

