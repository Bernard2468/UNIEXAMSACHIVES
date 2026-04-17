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
        Schema::create('system_maintenance_logs', function (Blueprint $table) {
            $table->id();
            
            // Maintenance details
            $table->enum('maintenance_type', [
                'scheduled_maintenance',
                'emergency_maintenance',
                'system_update',
                'security_patch',
                'database_optimization',
                'server_upgrade',
                'backup_restore',
                'other'
            ])->default('scheduled_maintenance');
            
            $table->string('title');
            $table->text('description');
            $table->text('technical_details')->nullable();
            
            // Scheduling
            $table->timestamp('scheduled_start');
            $table->timestamp('scheduled_end');
            $table->timestamp('actual_start')->nullable();
            $table->timestamp('actual_end')->nullable();
            
            // Status tracking
            $table->enum('status', [
                'planned',
                'notified',
                'in_progress',
                'completed',
                'cancelled',
                'failed'
            ])->default('planned');
            
            // Impact assessment
            $table->enum('impact_level', ['low', 'medium', 'high', 'critical'])->default('medium');
            $table->json('affected_services')->nullable(); // Array of affected services
            $table->boolean('requires_downtime')->default(false);
            $table->integer('estimated_downtime_minutes')->nullable();
            $table->integer('actual_downtime_minutes')->nullable();
            
            // Notification management
            $table->boolean('notification_sent_to_users')->default(false);
            $table->timestamp('notification_sent_at')->nullable();
            $table->integer('users_notified_count')->default(0);
            
            $table->boolean('completion_notification_sent')->default(false);
            $table->timestamp('completion_notification_sent_at')->nullable();
            
            // User display settings
            $table->boolean('display_banner')->default(true);
            $table->timestamp('banner_display_from')->nullable();
            $table->timestamp('banner_display_until')->nullable();
            $table->text('banner_message')->nullable();
            
            // Execution details
            $table->foreignId('performed_by')->constrained('users')->onDelete('cascade');
            $table->json('team_members')->nullable(); // Array of user IDs involved
            $table->text('completion_notes')->nullable();
            $table->text('issues_encountered')->nullable();
            
            // Rollback information
            $table->boolean('rollback_available')->default(false);
            $table->text('rollback_procedure')->nullable();
            $table->timestamp('rolled_back_at')->nullable();
            $table->foreignId('rolled_back_by')->nullable()->constrained('users')->onDelete('set null');
            $table->text('rollback_reason')->nullable();
            
            // Approval workflow (for emergency maintenance)
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            
            // Metadata
            $table->json('metadata')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('status');
            $table->index('maintenance_type');
            $table->index('scheduled_start');
            $table->index(['status', 'scheduled_start']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system_maintenance_logs');
    }
};

