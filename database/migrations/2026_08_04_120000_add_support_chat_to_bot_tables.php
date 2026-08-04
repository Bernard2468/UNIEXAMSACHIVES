<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Human-support layer on top of the existing bot tables.
 *
 * PURELY ADDITIVE: every column here is nullable or has a safe default, so the
 * existing MetaGuide bot (and its optional transcripts) keeps behaving exactly
 * as before. Pure-bot rows default to mode = 'bot' and never surface in the
 * support inbox (which filters on mode = 'support').
 *
 * Support conversations ARE persisted regardless of the `bot_store_transcripts`
 * setting — that toggle governs anonymous bot browsing only. The moment a user
 * escalates to a human, that single thread is stored so an agent can answer it.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bot_conversations', function (Blueprint $table) {
            // 'bot' = ordinary MetaGuide transcript · 'support' = human handoff thread
            $table->string('mode', 16)->default('bot')->after('page');
            // queued → active → resolved (null for pure bot rows)
            $table->string('status', 20)->nullable()->after('mode');
            $table->string('subject', 190)->nullable()->after('status');
            $table->string('category', 40)->nullable()->after('subject');
            $table->unsignedBigInteger('assigned_agent_id')->nullable()->after('category');
            $table->unsignedBigInteger('office_id')->nullable()->after('assigned_agent_id');
            $table->timestamp('last_user_message_at')->nullable()->after('office_id');
            $table->timestamp('last_agent_message_at')->nullable()->after('last_user_message_at');
            $table->timestamp('resolved_at')->nullable()->after('last_agent_message_at');
            $table->unsignedBigInteger('resolved_by')->nullable()->after('resolved_at');
            // unread counters, one per side, so badges are O(1)
            $table->unsignedInteger('user_unread')->default(0)->after('resolved_by');
            $table->unsignedInteger('agent_unread')->default(0)->after('user_unread');
            // bot-context snapshot at handoff, CSAT rating, misc flags
            $table->json('meta')->nullable()->after('agent_unread');

            $table->index(['mode', 'status']);
            $table->index('assigned_agent_id');
        });

        Schema::table('bot_messages', function (Blueprint $table) {
            // Precise sender for support threads: user | agent | bot | system.
            // The legacy `role` column (user|assistant) is still populated for
            // backward-compatible transcript rendering.
            $table->string('sender_type', 16)->nullable()->after('role');
            $table->unsignedBigInteger('sender_id')->nullable()->after('sender_type');
            // Client-generated id → idempotent posting (double-tap / retry safe).
            $table->string('client_id', 64)->nullable()->after('sender_id');
            // Agent-only internal note — never shown to the requesting user.
            $table->boolean('is_internal')->default(false)->after('client_id');
            $table->timestamp('read_at')->nullable()->after('is_internal');

            $table->index('client_id');
        });
    }

    public function down(): void
    {
        Schema::table('bot_conversations', function (Blueprint $table) {
            $table->dropIndex(['mode', 'status']);
            $table->dropIndex(['assigned_agent_id']);
            $table->dropColumn([
                'mode', 'status', 'subject', 'category', 'assigned_agent_id', 'office_id',
                'last_user_message_at', 'last_agent_message_at', 'resolved_at', 'resolved_by',
                'user_unread', 'agent_unread', 'meta',
            ]);
        });

        Schema::table('bot_messages', function (Blueprint $table) {
            $table->dropIndex(['client_id']);
            $table->dropColumn(['sender_type', 'sender_id', 'client_id', 'is_internal', 'read_at']);
        });
    }
};
