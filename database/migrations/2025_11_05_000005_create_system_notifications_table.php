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
        Schema::create('system_notifications', function (Blueprint $table) {
            $table->id();
            
            // Notification classification
            $table->enum('notification_type', [
                'renewal_reminder',
                'payment_success',
                'payment_failed',
                'subscription_expired',
                'subscription_suspended',
                'subscription_reactivated',
                'maintenance_scheduled',
                'maintenance_started',
                'maintenance_completed',
                'system_update',
                'security_alert',
                'feature_announcement',
                'important_notice',
                'general_info'
            ]);
            
            // Targeting
            $table->enum('target_audience', [
                'all_users',
                'admins_only',
                'super_admins_only',
                'specific_users',
                'subscription_based'
            ])->default('all_users');
            
            $table->json('custom_user_ids')->nullable(); // If target_audience = specific_users
            $table->foreignId('subscription_id')->nullable()->constrained('system_subscriptions')->onDelete('cascade'); // If subscription-based
            
            // Priority and urgency
            $table->enum('priority', ['low', 'medium', 'high', 'critical'])->default('medium');
            $table->boolean('requires_acknowledgment')->default(false);
            
            // Content
            $table->string('title');
            $table->text('message');
            $table->text('short_message')->nullable(); // For preview/banner
            $table->string('icon')->nullable(); // Icon class or emoji
            $table->string('color')->nullable(); // Badge color (success, warning, danger, info)
            
            // Action buttons
            $table->json('action_buttons')->nullable(); // [{label, url, style}]
            
            // Display settings
            $table->boolean('display_as_banner')->default(false);
            $table->boolean('display_in_notification_center')->default(true);
            $table->boolean('send_email')->default(false);
            $table->boolean('is_dismissible')->default(true);
            
            // Timing
            $table->timestamp('display_from')->nullable();
            $table->timestamp('display_until')->nullable();
            $table->boolean('is_active')->default(true);
            
            // Delivery tracking
            $table->integer('total_recipients')->default(0);
            $table->integer('read_count')->default(0);
            $table->integer('dismissed_count')->default(0);
            $table->integer('acknowledged_count')->default(0);
            $table->integer('email_sent_count')->default(0);
            
            // Email delivery
            $table->boolean('email_sent')->default(false);
            $table->timestamp('email_sent_at')->nullable();
            $table->text('email_template')->nullable(); // Custom email template
            
            // Related entities
            $table->foreignId('related_maintenance_id')->nullable()->constrained('system_maintenance_logs')->onDelete('cascade');
            $table->foreignId('related_transaction_id')->nullable()->constrained('payment_transactions')->onDelete('cascade');
            
            // Administrative
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->boolean('is_automated')->default(false); // System-generated vs manual
            
            // Metadata
            $table->json('metadata')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('notification_type');
            $table->index('target_audience');
            $table->index('priority');
            $table->index('is_active');
            $table->index(['is_active', 'display_from', 'display_until']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system_notifications');
    }
};

