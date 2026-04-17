<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Tracks when each user last "read" each memo (opened the chat).
     * Used to split pending memos into Active Chat (has new activity) vs Read (up to date).
     */
    public function up(): void
    {
        Schema::create('memo_user_reads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('campaign_id')->constrained('comm_campaigns')->onDelete('cascade');
            $table->timestamp('last_read_at');
            $table->timestamps();

            $table->unique(['user_id', 'campaign_id']);
            $table->index(['user_id', 'last_read_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('memo_user_reads');
    }
};
