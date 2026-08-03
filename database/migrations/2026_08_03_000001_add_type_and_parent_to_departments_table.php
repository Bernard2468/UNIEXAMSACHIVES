<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Turns the flat `departments` table into a shallow taxonomy WITHOUT touching any
 * existing row id — so every user assignment (users.department_id, the
 * department_user pivot, and departmental folder grants, all keyed by id) keeps
 * pointing at exactly the same row it did before. We only ADD descriptive columns.
 *
 *   type      'faculty'    → a Faculty OR School (top-level, may hold departments)
 *             'department' → sits UNDER a faculty (parent_id points at it)
 *             'unit'       → standalone (Directorate / Office / etc.), never nested
 *   parent_id the faculty a department belongs to (null for faculties + units)
 *
 * parent_id is intentionally a plain indexed column with NO database-level foreign
 * key: SQLite cannot add an FK to an existing table via ALTER, and we don't want a
 * cascade to ever delete a row that users are attached to. Integrity is enforced in
 * DepartmentController instead.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('departments', function (Blueprint $table) {
            $table->string('type')->default('unit')->after('name')->index();
            $table->unsignedBigInteger('parent_id')->nullable()->after('type')->index();
        });

        // Best-effort backfill so the new tabs aren't empty on day one. Anything
        // that looks like a faculty/school becomes a top-level container; everything
        // else defaults to a self-contained Unit (never looks "orphaned"). The admin
        // then re-types genuine departments and nests them from the new UI.
        DB::table('departments')
            ->where('name', 'like', '%faculty%')
            ->orWhere('name', 'like', '%school%')
            ->update(['type' => 'faculty', 'parent_id' => null]);
    }

    public function down(): void
    {
        Schema::table('departments', function (Blueprint $table) {
            $table->dropIndex(['parent_id']);
            $table->dropIndex(['type']);
            $table->dropColumn(['type', 'parent_id']);
        });
    }
};
