<?php

namespace App\Services;

use App\Models\PaymentTransaction;
use App\Models\SystemSubscription;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class PaystackService
{
    private string $baseUrl = 'https://api.paystack.co';
    private ?string $secretKey;
    private ?string $publicKey;

    public function __construct()
    {
        // Get Paystack keys from system settings (configured in super admin dashboard)
        $this->secretKey = SystemSetting::getPaystackSecretKey();
        $this->publicKey = SystemSetting::getPaystackPublicKey();
    }

    /**
     * Get Paystack public key for frontend
     */
    public function getPublicKey(): ?string
    {
        return $this->publicKey;
    }

    /**
     * Check if Paystack is properly configured
     */
    public function isConfigured(): bool
    {
        return !empty($this->secretKey) && !empty($this->publicKey);
    }

    /**
     * Initialize a payment transaction
     * 
     * @param PaymentTransaction $transaction
     * @param string $callbackUrl
     * @return array
     */
    public function initializePayment(PaymentTransaction $transaction, string $callbackUrl): array
    {
        if (!$this->isConfigured()) {
            throw new Exception('Paystack is not configured. Please add your API keys in Super Admin Settings.');
        }

        $amountInKobo = $this->convertToKobo($transaction->amount);

        $payload = [
            'email' => $transaction->customer_email,
            'amount' => $amountInKobo,
            'currency' => $transaction->currency,
            'reference' => $transaction->transaction_reference,
            'callback_url' => $callbackUrl,
            'metadata' => [
                'transaction_id' => $transaction->id,
                'subscription_id' => $transaction->subscription_id,
                'transaction_type' => $transaction->transaction_type,
                'customer_name' => $transaction->customer_name,
                'custom_fields' => [
                    [
                        'display_name' => 'Transaction Type',
                        'variable_name' => 'transaction_type',
                        'value' => ucwords(str_replace('_', ' ', $transaction->transaction_type))
                    ],
                    [
                        'display_name' => 'Institution',
                        'variable_name' => 'institution',
                        'value' => $transaction->subscription->institution_name ?? 'N/A'
                    ]
                ]
            ]
        ];

        // If auto-renewal, add channels for card only
        if ($transaction->is_auto_payment) {
            $payload['channels'] = ['card'];
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->secretKey,
                'Content-Type' => 'application/json',
            ])->post("{$this->baseUrl}/transaction/initialize", $payload);

            $data = $response->json();

            if ($response->successful() && $data['status']) {
                // Update transaction with gateway reference
                $transaction->update([
                    'gateway_reference' => $data['data']['reference'],
                    'gateway_response' => json_encode($data),
                    'status' => 'pending',
                ]);

                return [
                    'success' => true,
                    'authorization_url' => $data['data']['authorization_url'],
                    'access_code' => $data['data']['access_code'],
                    'reference' => $data['data']['reference'],
                ];
            }

            throw new Exception($data['message'] ?? 'Payment initialization failed');

        } catch (Exception $e) {
            Log::error('Paystack initialization error', [
                'transaction_id' => $transaction->id,
                'error' => $e->getMessage()
            ]);

            $transaction->markAsFailed('Initialization failed: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Verify a payment transaction
     * 
     * @param string $reference
     * @return array
     */
    public function verifyPayment(string $reference): array
    {
        if (!$this->isConfigured()) {
            throw new Exception('Paystack is not configured.');
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->secretKey,
            ])->get("{$this->baseUrl}/transaction/verify/{$reference}");

            $data = $response->json();

            if ($response->successful() && $data['status']) {
                return [
                    'success' => true,
                    'data' => $data['data'],
                ];
            }

            return [
                'success' => false,
                'message' => $data['message'] ?? 'Payment verification failed',
            ];

        } catch (Exception $e) {
            Log::error('Paystack verification error', [
                'reference' => $reference,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Handle webhook notification from Paystack
     * 
     * @param array $payload
     * @return bool
     */
    public function handleWebhook(array $payload): bool
    {
        try {
            $event = $payload['event'] ?? null;

            if (!$event) {
                Log::warning('Paystack webhook received without event type');
                return false;
            }

            Log::info('Paystack webhook received', ['event' => $event]);

            switch ($event) {
                case 'charge.success':
                    return $this->handleSuccessfulCharge($payload['data']);

                case 'subscription.create':
                    return $this->handleSubscriptionCreated($payload['data']);

                case 'subscription.disable':
                    return $this->handleSubscriptionDisabled($payload['data']);

                case 'invoice.create':
                case 'invoice.update':
                    return $this->handleInvoiceEvent($payload['data']);

                default:
                    Log::info('Unhandled Paystack webhook event', ['event' => $event]);
                    return true;
            }

        } catch (Exception $e) {
            Log::error('Paystack webhook handling error', [
                'error' => $e->getMessage(),
                'payload' => $payload
            ]);
            return false;
        }
    }

    /**
     * Charge an authorization (for recurring payments)
     * 
     * @param PaymentTransaction $transaction
     * @param string $authorizationCode
     * @return array
     */
    public function chargeAuthorization(PaymentTransaction $transaction, string $authorizationCode): array
    {
        if (!$this->isConfigured()) {
            throw new Exception('Paystack is not configured.');
        }

        $amountInKobo = $this->convertToKobo($transaction->amount);

        $payload = [
            'email' => $transaction->customer_email,
            'amount' => $amountInKobo,
            'authorization_code' => $authorizationCode,
            'reference' => $transaction->transaction_reference,
            'currency' => $transaction->currency,
            'metadata' => [
                'transaction_id' => $transaction->id,
                'subscription_id' => $transaction->subscription_id,
            ]
        ];

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->secretKey,
                'Content-Type' => 'application/json',
            ])->post("{$this->baseUrl}/transaction/charge_authorization", $payload);

            $data = $response->json();

            if ($response->successful() && $data['status'] && $data['data']['status'] === 'success') {
                $transaction->markAsCompleted($data);

                return [
                    'success' => true,
                    'data' => $data['data'],
                ];
            }

            $failureReason = $data['message'] ?? 'Charge authorization failed';
            $transaction->markAsFailed($failureReason, $data);

            return [
                'success' => false,
                'message' => $failureReason,
            ];

        } catch (Exception $e) {
            Log::error('Paystack charge authorization error', [
                'transaction_id' => $transaction->id,
                'error' => $e->getMessage()
            ]);

            $transaction->markAsFailed('Charge failed: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Create a subscription plan
     * 
     * @param string $name
     * @param float $amount
     * @param string $interval (monthly, quarterly, annually)
     * @return array
     */
    public function createPlan(string $name, float $amount, string $interval = 'annually'): array
    {
        if (!$this->isConfigured()) {
            throw new Exception('Paystack is not configured.');
        }

        $amountInKobo = $this->convertToKobo($amount);
        
        // Convert interval to Paystack format
        $paystackInterval = match($interval) {
            'monthly' => 'monthly',
            'quarterly' => 'quarterly',
            'semi_annual' => 'biannually',
            'annual' => 'annually',
            default => 'annually'
        };

        $payload = [
            'name' => $name,
            'amount' => $amountInKobo,
            'interval' => $paystackInterval,
            'currency' => 'GHS',
        ];

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->secretKey,
                'Content-Type' => 'application/json',
            ])->post("{$this->baseUrl}/plan", $payload);

            $data = $response->json();

            if ($response->successful() && $data['status']) {
                return [
                    'success' => true,
                    'plan_code' => $data['data']['plan_code'],
                    'data' => $data['data'],
                ];
            }

            return [
                'success' => false,
                'message' => $data['message'] ?? 'Plan creation failed',
            ];

        } catch (Exception $e) {
            Log::error('Paystack plan creation error', [
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Validate Paystack webhook signature
     * 
     * @param string $payload
     * @param string $signature
     * @return bool
     */
    public function validateWebhookSignature(string $payload, string $signature): bool
    {
        $webhookSecret = SystemSetting::getPaystackWebhookSecret();
        
        if (!$webhookSecret) {
            Log::warning('Paystack webhook secret not configured');
            return false;
        }

        $computedSignature = hash_hmac('sha512', $payload, $webhookSecret);
        
        return hash_equals($computedSignature, $signature);
    }

    // ===== Private Helper Methods =====

    private function convertToKobo(float $amount): int
    {
        return (int) ($amount * 100);
    }

    private function convertFromKobo(int $kobo): float
    {
        return $kobo / 100;
    }

    private function handleSuccessfulCharge(array $data): bool
    {
        $reference = $data['reference'] ?? null;
        
        if (!$reference) {
            return false;
        }

        $transaction = PaymentTransaction::where('transaction_reference', $reference)
            ->orWhere('gateway_reference', $reference)
            ->first();

        if (!$transaction) {
            Log::warning('Transaction not found for webhook', ['reference' => $reference]);
            return false;
        }

        if ($transaction->status === 'completed') {
            return true; // Already processed
        }

        // Save authorization code for future recurring payments
        if (isset($data['authorization']['authorization_code'])) {
            $transaction->authorization_code = $data['authorization']['authorization_code'];
        }

        $transaction->markAsCompleted($data);

        Log::info('Payment completed via webhook', [
            'transaction_id' => $transaction->id,
            'reference' => $reference
        ]);

        return true;
    }

    private function handleSubscriptionCreated(array $data): bool
    {
        Log::info('Paystack subscription created', ['data' => $data]);
        // Handle subscription creation if needed
        return true;
    }

    private function handleSubscriptionDisabled(array $data): bool
    {
        Log::info('Paystack subscription disabled', ['data' => $data]);
        
        // Find subscription by payment gateway subscription ID
        $subscriptionCode = $data['subscription_code'] ?? null;
        
        if ($subscriptionCode) {
            $subscription = SystemSubscription::where('payment_gateway_subscription_id', $subscriptionCode)->first();
            
            if ($subscription) {
                $subscription->update(['auto_renewal' => false]);
            }
        }
        
        return true;
    }

    private function handleInvoiceEvent(array $data): bool
    {
        Log::info('Paystack invoice event', ['data' => $data]);
        // Handle invoice events if needed
        return true;
    }

    /**
     * Get payment method from Paystack channel
     */
    private function getPaymentMethodFromChannel(?string $channel): ?string
    {
        return match($channel) {
            'card' => 'card',
            'mobile_money' => 'mobile_money_mtn', // Default to MTN
            'bank' => 'bank_transfer',
            default => null
        };
    }
}

