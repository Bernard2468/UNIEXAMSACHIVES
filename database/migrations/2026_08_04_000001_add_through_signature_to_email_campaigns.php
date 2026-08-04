<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Signed "Through" endorsement. Forwarding a Through memo is a one-time
 * official act by the named intermediary, so its signature snapshot, remark
 * and timestamp live on the campaign itself (unlike minutes, which repeat and
 * live in memo_minutes). The snapshot file is per-event, so later changes to
 * the user's saved signature never rewrite what was signed.
 */
return new class extends Migration
{
    public function up(): void
    {
        // NB: the EmailCampaign model's table is comm_campaigns (NOT the
        // legacy email_campaigns table) — same target as the migration that
        // added through_user_id / through_status.
        Schema::table('comm_campaigns', function (Blueprint $table) {
            $table->string('through_signature_path')->nullable()->after('through_status');
            $table->timestamp('through_signed_at')->nullable()->after('through_signature_path');
            $table->text('through_remark')->nullable()->after('through_signed_at');
        });
    }

    public function down(): void
    {
        Schema::table('comm_campaigns', function (Blueprint $table) {
            $table->dropColumn(['through_signature_path', 'through_signed_at', 'through_remark']);
        });
    }
};
