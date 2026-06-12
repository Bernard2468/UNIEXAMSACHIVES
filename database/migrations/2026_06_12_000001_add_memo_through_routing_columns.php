<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * "Through" routing for memos.
 *
 * Lets a memo be addressed to its real recipient(s) but routed *through* an
 * intermediary first (e.g. To: Vice-Chancellor, Through: Pro Vice-Chancellor).
 * The intermediary receives it, reviews/minutes it, then forwards it onward —
 * only then do the real recipients (and Cc) receive it.
 *
 * Both columns are NULLABLE with no default behaviour change:
 *  - a memo with no Through person leaves through_user_id null and behaves
 *    exactly as before (immediate broadcast to all recipients),
 *  - nothing in the existing flow reads these columns unless a memo opts in.
 *
 * The held recipient list is NOT duplicated here — it is re-derived at forward
 * time from the memo's existing `selected_users` (To) and `cc_users` (Cc),
 * since Through is only offered in "Selected Users" mode.
 *
 * No DB-level foreign key is added on purpose, mirroring the project's other
 * memo columns: SQLite (the default driver) cannot add FK constraints to an
 * existing table via ALTER, so the relationship is enforced at the application
 * layer. The column is indexed so the "memos awaiting my forward" lookup stays
 * fast.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('comm_campaigns', function (Blueprint $table) {
            // The intermediary the memo is routed through. null = no Through.
            $table->unsignedBigInteger('through_user_id')->nullable()->after('cc_users');

            // null = not a Through memo | 'pending' = awaiting the intermediary
            // to forward | 'forwarded' = released to the real recipients.
            $table->string('through_status')->nullable()->after('through_user_id');

            $table->index('through_user_id');
            $table->index('through_status');
        });
    }

    public function down(): void
    {
        Schema::table('comm_campaigns', function (Blueprint $table) {
            $table->dropIndex(['through_user_id']);
            $table->dropIndex(['through_status']);
            $table->dropColumn(['through_user_id', 'through_status']);
        });
    }
};
