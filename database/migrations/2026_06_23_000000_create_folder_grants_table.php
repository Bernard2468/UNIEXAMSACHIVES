<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Group / audience grants for folders.
     *
     * Separate from `folder_shares` (which stays user-only) so existing direct
     * shares and link-joiners are untouched. A grant references an *audience*
     * (a group resolved live at access time) rather than a fixed user:
     *
     *   audience_type  = department | staff_category | committee | office
     *                    | leadership | everyone | (future types…)
     *   audience_value = the specific group within that type
     *                    (department id, 'Senior Staff', committee id, office id,
     *                     'hod'/'dean'/'director', or '' for "everyone")
     *
     * Membership is NOT snapshotted: who is "in" the group is computed at the
     * moment access is checked, so people joining/leaving the group gain/lose
     * access automatically.
     */
    public function up(): void
    {
        Schema::create('folder_grants', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('folder_id');
            $table->string('audience_type', 32);
            // Kept NOT NULL with a '' default so the unique index below behaves
            // consistently across SQLite/MySQL (NULLs are treated as distinct).
            $table->string('audience_value', 191)->default('');
            $table->string('permission', 16)->default('viewer'); // 'viewer' | 'editor'
            $table->unsignedBigInteger('shared_by')->nullable();
            $table->timestamps();

            $table->foreign('folder_id')->references('id')->on('folders')->onDelete('cascade');
            $table->foreign('shared_by')->references('id')->on('users')->onDelete('set null');

            $table->unique(['folder_id', 'audience_type', 'audience_value'], 'folder_grants_unique');
            $table->index(['audience_type', 'audience_value']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('folder_grants');
    }
};
