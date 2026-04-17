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
        Schema::create('system_subscriptions', function (Blueprint $table) {
            $table->id();
            
            // Institution details
            $table->string('institution_name');
            $table->string('institution_code')->unique()->nullable();
            
            // Subscription plan details
            $table->enum('subscription_plan', ['basic', 'standard', 'premium', 'enterprise'])->default('standard');
            $table->date('subscription_start_date');
            $table->date('subscription_end_date');
            
            // Renewal configuration
            $table->enum('renewal_cycle', ['monthly', 'quarterly', 'semi_annual', 'annual'])->default('annual');
            $table->decimal('renewal_amount', 10, 2);
            $table->string('currency', 3)->default('GHS');
            
            // Hosting details
            $table->string('hosting_package_type')->nullable();
            $table->text('package_features')->nullable(); // JSON encoded features
            
            // Status and automation
            $table->enum('status', ['active', 'expiring_soon', 'expired', 'suspended', 'cancelled'])->default('active');
            $table->boolean('auto_renewal')->default(true);
            $table->integer('grace_period_days')->default(7);
            
            // Payment gateway integration
            $table->string('payment_gateway_subscription_id')->nullable();
            $table->string('payment_gateway_customer_id')->nullable();
            
            // Payment dates tracking
            $table->timestamp('last_payment_date')->nullable();
            $table->timestamp('next_payment_date')->nullable();
            $table->integer('failed_payment_attempts')->default(0);
            $table->timestamp('last_failed_payment_at')->nullable();
            
            // Notification tracking
            $table->boolean('renewal_reminder_30_days_sent')->default(false);
            $table->boolean('renewal_reminder_14_days_sent')->default(false);
            $table->boolean('renewal_reminder_7_days_sent')->default(false);
            $table->boolean('renewal_reminder_1_day_sent')->default(false);
            $table->boolean('expiry_notification_sent')->default(false);
            
            // Administrative tracking
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            
            // Notes and metadata
            $table->text('admin_notes')->nullable();
            $table->json('metadata')->nullable(); // Additional custom data
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes for performance
            $table->index('status');
            $table->index('subscription_end_date');
            $table->index('next_payment_date');
            $table->index(['status', 'subscription_end_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system_subscriptions');
    }
};

