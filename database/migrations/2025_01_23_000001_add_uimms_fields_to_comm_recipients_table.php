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
        Schema::table('comm_recipients', function (Blueprint $table) {
            // UIMMS specific fields - all nullable to not break existing data
            $table->boolean('is_active_participant')->default(true)->after('error_message');
            $table->timestamp('assigned_at')->nullable()->after('is_active_participant');
            $table->timestamp('last_activity_at')->nullable()->after('assigned_at');
            $table->json('participation_history')->nullable()->after('last_activity_at');
            
            // Add indexes for performance
            $table->index(['is_active_participant']);
            $table->index(['assigned_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('comm_recipients', function (Blueprint $table) {
            $table->dropIndex(['is_active_participant']);
            $table->dropIndex(['assigned_at']);
            
            $table->dropColumn([
                'is_active_participant',
                'assigned_at',
                'last_activity_at',
                'participation_history'
            ]);
        });
    }
};
