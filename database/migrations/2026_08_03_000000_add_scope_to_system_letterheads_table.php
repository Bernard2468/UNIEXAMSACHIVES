<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Scope a letterhead to a single org unit. Both columns NULL = "Everyone"
     * (global letterhead, e.g. the CUG Official header). Otherwise:
     *   scope_type = 'department'  -> scope_id references departments.id
     *                                 (a faculty/school, department, or unit)
     *   scope_type = 'office'      -> scope_id references offices.id
     * Kept as a plain token + id (not a polymorphic morph) so the visibility
     * query stays readable and no global morph map is required.
     */
    public function up(): void
    {
        Schema::table('system_letterheads', function (Blueprint $table) {
            $table->string('scope_type')->nullable()->after('description');
            $table->unsignedBigInteger('scope_id')->nullable()->after('scope_type');
            $table->index(['scope_type', 'scope_id']);
        });
    }

    public function down(): void
    {
        Schema::table('system_letterheads', function (Blueprint $table) {
            $table->dropIndex(['scope_type', 'scope_id']);
            $table->dropColumn(['scope_type', 'scope_id']);
        });
    }
};
