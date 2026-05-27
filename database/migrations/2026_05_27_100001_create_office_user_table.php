<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Pivot table linking users to the offices they belong to.
     *
     * - is_head designates the primary recipient for forms routed to the office.
     * - is_active lets us soft-disable a membership without deleting the row
     *   (preserves form history that referenced this assignee).
     */
    public function up(): void
    {
        Schema::create('office_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('office_id')->constrained('offices')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->boolean('is_head')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['office_id', 'user_id']);
            $table->index(['office_id', 'is_head']);
            $table->index(['user_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('office_user');
    }
};
