<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Frozen "as approved" memo snapshot for a form.
 *
 * When a form is started from an approved memo (source_campaign_id), we render
 * that memo's print/export PDF once and store it here, so everyone in the form
 * trail (HODs, offices, signers, commenters) can open the exact approval that
 * authorised the form — without being participants in the originating memo.
 *
 * Nullable + best-effort: if the snapshot is missing (older forms, or a failed
 * render) the form route falls back to rendering the memo live, so the feature
 * degrades gracefully and never blocks form access.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('form_submissions', function (Blueprint $table) {
            $table->string('source_memo_pdf_path')->nullable()->after('source_campaign_id');
        });
    }

    public function down(): void
    {
        Schema::table('form_submissions', function (Blueprint $table) {
            $table->dropColumn('source_memo_pdf_path');
        });
    }
};
