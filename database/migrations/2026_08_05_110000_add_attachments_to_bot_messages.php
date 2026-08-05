<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * File attachments on support-chat messages (user ↔ agent).
 *
 * Additive + nullable. Files are stored under storage/app/public/support/{conv}/
 * with a randomised name; the original name/mime/size live here for display, and
 * the file is served through a policy-authorized download route (never the open
 * /storage route), so only conversation participants can fetch it.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bot_messages', function (Blueprint $table) {
            $table->string('attachment_path')->nullable()->after('read_at');
            $table->string('attachment_name')->nullable()->after('attachment_path');
            $table->string('attachment_mime', 150)->nullable()->after('attachment_name');
            $table->unsignedBigInteger('attachment_size')->nullable()->after('attachment_mime');
        });
    }

    public function down(): void
    {
        Schema::table('bot_messages', function (Blueprint $table) {
            $table->dropColumn(['attachment_path', 'attachment_name', 'attachment_mime', 'attachment_size']);
        });
    }
};
