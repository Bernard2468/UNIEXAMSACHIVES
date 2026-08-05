<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * User-side soft delete for support conversations.
 *
 * When a user clears a chat from their widget history we set `user_deleted_at`
 * — it disappears from THEIR history/active views, but the agent inbox keeps the
 * full record (so nothing an agent handled is ever lost). If support later
 * replies to the same open thread, the flag is cleared so the user is never
 * ghosted. Additive + nullable, so existing rows are unaffected.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bot_conversations', function (Blueprint $table) {
            $table->timestamp('user_deleted_at')->nullable()->after('resolved_by');
        });
    }

    public function down(): void
    {
        Schema::table('bot_conversations', function (Blueprint $table) {
            $table->dropColumn('user_deleted_at');
        });
    }
};
