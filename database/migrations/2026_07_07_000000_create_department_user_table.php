<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Pivot table for a user's SECONDARY departments.
     *
     * The user's PRIMARY department stays on users.department_id (unchanged) and
     * continues to drive Forms routing. This pivot holds the optional extra
     * departments a member is attached to — e.g. someone whose home department is
     * Computer Engineering but who teaches/takes a course in Nursing. Secondary
     * departments grant departmental-folder visibility but are intentionally kept
     * out of Forms leadership/VC routing (that must stay deterministic).
     */
    public function up(): void
    {
        Schema::create('department_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('department_id')->constrained('departments')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['department_id', 'user_id']);
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('department_user');
    }
};
