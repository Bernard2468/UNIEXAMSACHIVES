<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('payment_transactions', function (Blueprint $table) {
            $table->id();
            
            // Relationships
            $table->foreignId('subscription_id')->constrained('system_subscriptions')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null'); // Who initiated payment
            
            // Transaction details
            $table->enum('transaction_type', [
                'subscription_new', 
                'subscription_renewal', 
                'subscription_upgrade', 
                'subscription_downgrade',
                'maintenance_fee',
                'addon_service',
                'refund'
            ])->default('subscription_renewal');
            
            // Amount details
            $table->decimal('amount', 10, 2);
            $table->decimal('original_amount', 10, 2)->nullable(); // Before discounts
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->string('discount_code')->nullable();
            $table->string('currency', 3)->default('GHS');
            
            // Payment method and gateway
            $table->enum('payment_method', [
                'card',
                'mobile_money_mtn',
                'mobile_money_vodafone',
                'mobile_money_airteltigo',
                'bank_transfer',
                'cash',
                'other'
            ])->nullable();
            
            $table->enum('payment_gateway', ['paystack', 'flutterwave', 'stripe', 'manual', 'other'])->default('paystack');
            
            // Gateway response data
            $table->string('transaction_reference')->unique();
            $table->string('gateway_reference')->nullable();
            $table->text('gateway_response')->nullable(); // JSON encoded response
            $table->string('authorization_code')->nullable(); // For recurring payments
            
            // Transaction status
            $table->enum('status', [
                'pending',
                'processing',
                'completed',
                'failed',
                'cancelled',
                'refunded',
                'disputed'
            ])->default('pending');
            
            $table->text('failure_reason')->nullable();
            $table->integer('retry_count')->default(0);
            $table->timestamp('last_retry_at')->nullable();
            
            // Important dates
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('refunded_at')->nullable();
            
            // Receipt and invoice
            $table->string('invoice_number')->unique()->nullable();
            $table->string('receipt_url')->nullable();
            $table->string('invoice_pdf_path')->nullable();
            
            // Customer details (snapshot at time of payment)
            $table->string('customer_name')->nullable();
            $table->string('customer_email')->nullable();
            $table->string('customer_phone')->nullable();
            
            // Administrative
            $table->foreignId('processed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->text('admin_notes')->nullable();
            $table->boolean('is_auto_payment')->default(false);
            
            // Metadata
            $table->json('metadata')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index('status');
            $table->index('transaction_reference');
            $table->index('gateway_reference');
            $table->index('invoice_number');
            $table->index(['subscription_id', 'status']);
            $table->index(['transaction_type', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_transactions');
    }
};

