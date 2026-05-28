<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('push_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            // Endpoint URL the browser gave us. Unique per user-device pairing.
            $table->text('endpoint');
            // The two crypto keys the push provider needs to encrypt payloads.
            $table->string('p256dh_key', 255);
            $table->string('auth_key', 100);
            // Optional content encoding (aesgcm / aes128gcm) reported by browser.
            $table->string('content_encoding', 32)->nullable();
            // Useful UA fingerprint for the user's "Devices" list.
            $table->string('user_agent', 500)->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamps();

            $table->index('user_id');
            $table->index('last_used_at');
            // Same endpoint can't be subscribed twice for one user.
            $table->unique(['user_id', 'endpoint'], 'push_subs_user_endpoint_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('push_subscriptions');
    }
};
