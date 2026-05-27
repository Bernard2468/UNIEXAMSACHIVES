<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Adds the timestamp the SLA-nudge cron uses to remember when it last
     * emailed about a stuck submission. Without this column we would spam
     * the assignee every time the scheduler runs.
     *
     * The column is written via DB::update() in the nudge command (not via
     * Eloquent save()) so it does NOT bump form_submissions.updated_at —
     * otherwise nudging a stale form would reset its own staleness counter.
     */
    public function up(): void
    {
        Schema::table('form_submissions', function (Blueprint $table) {
            $table->timestamp('last_nudged_at')->nullable()->after('archived_at');
        });
    }

    public function down(): void
    {
        Schema::table('form_submissions', function (Blueprint $table) {
            $table->dropColumn('last_nudged_at');
        });
    }
};
