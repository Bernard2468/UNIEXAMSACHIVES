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
        Schema::table('comm_campaigns', function (Blueprint $table) {
            // UIMMS specific fields - all nullable to not break existing data
            $table->enum('memo_status', ['pending', 'suspended', 'completed', 'archived'])->nullable()->after('status');
            $table->foreignId('current_assignee_id')->nullable()->constrained('users')->onDelete('set null')->after('created_by');
            $table->foreignId('original_sender_id')->nullable()->constrained('users')->onDelete('set null')->after('current_assignee_id');
            $table->string('assigned_to_office')->nullable()->after('original_sender_id');
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium')->after('assigned_to_office');
            $table->timestamp('due_date')->nullable()->after('priority');
            $table->timestamp('completed_at')->nullable()->after('due_date');
            $table->timestamp('suspended_at')->nullable()->after('completed_at');
            $table->timestamp('archived_at')->nullable()->after('suspended_at');
            $table->json('workflow_history')->nullable()->after('archived_at');
            
            // Add indexes for performance
            $table->index(['memo_status']);
            $table->index(['current_assignee_id']);
            $table->index(['original_sender_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('comm_campaigns', function (Blueprint $table) {
            $table->dropIndex(['memo_status']);
            $table->dropIndex(['current_assignee_id']);
            $table->dropIndex(['original_sender_id']);
            
            $table->dropForeign(['current_assignee_id']);
            $table->dropForeign(['original_sender_id']);
            
            $table->dropColumn([
                'memo_status',
                'current_assignee_id', 
                'original_sender_id',
                'assigned_to_office',
                'priority',
                'due_date',
                'completed_at',
                'suspended_at',
                'archived_at',
                'workflow_history'
            ]);
        });
    }
};
