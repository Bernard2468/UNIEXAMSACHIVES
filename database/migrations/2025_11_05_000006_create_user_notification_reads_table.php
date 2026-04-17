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
        Schema::create('user_notification_reads', function (Blueprint $table) {
            $table->id();
            
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('system_notification_id')->constrained('system_notifications')->onDelete('cascade');
            
            // Interaction tracking
            $table->timestamp('read_at')->nullable();
            $table->timestamp('dismissed_at')->nullable();
            $table->timestamp('acknowledged_at')->nullable();
            
            // Interaction details
            $table->string('device_type')->nullable(); // web, mobile, etc.
            $table->string('ip_address')->nullable();
            
            $table->timestamps();
            
            // Composite unique index
            $table->unique(['user_id', 'system_notification_id'], 'user_notification_unique');
            
            // Indexes
            $table->index('user_id');
            $table->index('system_notification_id');
            $table->index(['user_id', 'read_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_notification_reads');
    }
};

