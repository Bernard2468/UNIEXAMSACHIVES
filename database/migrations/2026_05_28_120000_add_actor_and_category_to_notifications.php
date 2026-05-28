<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            // Actor = the user whose action triggered this notification. Used to
            // render an avatar next to the notification in the tray. Nullable so
            // system-generated notifications (cron, completion) don't need one.
            $table->foreignId('actor_id')->nullable()->after('user_id')
                ->constrained('users')->nullOnDelete();

            // Category drives the tray filter chips (All / Forms / Memos / etc.)
            // It is derivable from `type` but storing it keeps queries cheap.
            $table->string('category', 32)->nullable()->after('type')->index();
        });

        Schema::table('users', function (Blueprint $table) {
            // Last time the user opened the notification tray — drives the
            // "X new since you were last here" pill.
            $table->timestamp('last_tray_seen_at')->nullable()->after('updated_at');
            // Per-user toggle for browser push.
            $table->boolean('push_enabled')->default(true)->after('last_tray_seen_at');
        });
    }

    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropConstrainedForeignId('actor_id');
            $table->dropColumn('category');
        });
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['last_tray_seen_at', 'push_enabled']);
        });
    }
};
