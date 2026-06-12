<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Memo → Forms bridge.
 *
 * Lets a memo declare an optional "type" (promotion / procurement / leave /
 * other). When an approver explicitly unlocks the form, the original requester
 * is invited to proceed to the matching form.
 *
 * Every column here is NULLABLE with no default behaviour change:
 *  - existing memos and ordinary/general memos simply leave memo_category null,
 *  - nothing in the current workflow reads these columns unless a memo opts in.
 *
 * No DB-level foreign keys are added on purpose: SQLite (the default driver)
 * cannot add FK constraints to an existing table via ALTER, and the
 * relationships are enforced at the application layer. Columns are indexed so
 * the "unlocked memos for user" and "forms from memo" lookups stay fast.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('comm_campaigns', function (Blueprint $table) {
            // promotion | procurement | leave | other | null (general memo)
            $table->string('memo_category')->nullable()->after('reference');

            // Set the moment an approver unlocks the linked form for the requester.
            $table->timestamp('form_unlocked_at')->nullable()->after('memo_category');
            $table->unsignedBigInteger('form_unlocked_by')->nullable()->after('form_unlocked_at');

            $table->index('memo_category');
        });

        Schema::table('form_submissions', function (Blueprint $table) {
            // The memo this form was started from (traceability + duplicate guard).
            $table->unsignedBigInteger('source_campaign_id')->nullable()->after('created_by');
            $table->index('source_campaign_id');
        });
    }

    public function down(): void
    {
        Schema::table('comm_campaigns', function (Blueprint $table) {
            $table->dropIndex(['memo_category']);
            $table->dropColumn(['memo_category', 'form_unlocked_at', 'form_unlocked_by']);
        });

        Schema::table('form_submissions', function (Blueprint $table) {
            $table->dropIndex(['source_campaign_id']);
            $table->dropColumn('source_campaign_id');
        });
    }
};
