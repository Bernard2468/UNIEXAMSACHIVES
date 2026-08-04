<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Per-user, per-day usage counters for the UDTS Assistant bot.
 *
 * PRIVACY: counts only — never message content. This one table powers both the
 * per-user daily Gemini cap and the aggregate (institution-wide) analytics on the
 * Super Admin Bot page. `api_calls` is the only column that reflects paid usage;
 * kb/live/cache answers are free and do not count against a user's cap.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bot_usage_daily', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->date('date');
            $table->unsignedInteger('messages')->default(0);      // total questions asked
            $table->unsignedInteger('kb_answers')->default(0);    // answered from knowledge base
            $table->unsignedInteger('live_answers')->default(0);  // answered from live DB data
            $table->unsignedInteger('cache_hits')->default(0);    // answered from cached Gemini reply
            $table->unsignedInteger('api_calls')->default(0);     // hit Gemini (counts against cap)
            $table->unsignedInteger('latency_ms_sum')->default(0);// for avg latency (sum / messages)
            $table->timestamps();

            $table->unique(['user_id', 'date']);
            $table->index('date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bot_usage_daily');
    }
};
