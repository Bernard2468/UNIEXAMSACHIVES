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
        Schema::create('memo_user_bookmarks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('campaign_id')->constrained('comm_campaigns')->onDelete('cascade');
            $table->timestamps();
            
            // Ensure a user can only bookmark a memo once
            $table->unique(['user_id', 'campaign_id']);
            
            // Add indexes for performance
            $table->index(['user_id', 'created_at']);
            $table->index('campaign_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('memo_user_bookmarks');
    }
};

