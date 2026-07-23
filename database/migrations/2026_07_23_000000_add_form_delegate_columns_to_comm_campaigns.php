<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Memo → Forms delegation ("Assign to Procurement").
 *
 * After a procurement memo is approved & unlocked, the originator may hand
 * the actual form-filling off to somebody else (a Procurement office member
 * or any other user). That person becomes the memo's "form delegate": they
 * gain chat access and may start the linked form(s) on the originator's
 * behalf via ?source_campaign.
 *
 * Every column is NULLABLE with no default behaviour change — memos without
 * a delegation leave them null and nothing else reads them. As with the
 * other memo→forms columns, no DB-level foreign keys on purpose (SQLite
 * cannot ALTER-add FK constraints); relationships are enforced at the
 * application layer.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('comm_campaigns', function (Blueprint $table) {
            $table->unsignedBigInteger('form_delegate_id')->nullable()->after('form_unlocked_by');
            $table->unsignedBigInteger('form_delegated_by')->nullable()->after('form_delegate_id');
            $table->timestamp('form_delegated_at')->nullable()->after('form_delegated_by');

            $table->index('form_delegate_id');
        });
    }

    public function down(): void
    {
        Schema::table('comm_campaigns', function (Blueprint $table) {
            $table->dropIndex(['form_delegate_id']);
            $table->dropColumn(['form_delegate_id', 'form_delegated_by', 'form_delegated_at']);
        });
    }
};
