<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Optional full transcript storage — written ONLY when the Super Admin turns on
 * `bot_store_transcripts` (default OFF). With the toggle off these tables stay
 * empty and no message content is ever persisted, satisfying the requirement that
 * we do not track/log what users actually ask the bot.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bot_conversations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('page')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
        });

        Schema::create('bot_messages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('conversation_id');
            $table->string('role', 16);          // user | assistant
            $table->longText('content');
            $table->string('source', 16)->nullable(); // live | kb | cache | api
            $table->timestamps();

            $table->index('conversation_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bot_messages');
        Schema::dropIfExists('bot_conversations');
    }
};
