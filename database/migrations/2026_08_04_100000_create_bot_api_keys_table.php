<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Safe vault for AI provider API keys used by the UDTS Assistant bot.
 *
 * Keys are stored ENCRYPTED (Crypt::encryptString) in `key_encrypted` and are
 * never rendered in full in the UI (only ••••last4). Multiple active keys form
 * a rotation pool so provider rate limits are spread across keys — mirroring the
 * model-cascade strategy in GNRS's bright-handler edge function.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bot_api_keys', function (Blueprint $table) {
            $table->id();
            $table->string('label')->nullable();
            $table->string('provider')->default('gemini'); // gemini | deepseek
            $table->text('key_encrypted');
            $table->string('last4', 8)->nullable();        // for masked display only
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_used_at')->nullable();
            $table->unsignedInteger('request_count')->default(0);
            $table->unsignedInteger('failure_count')->default(0);
            $table->timestamps();

            $table->index(['provider', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bot_api_keys');
    }
};
