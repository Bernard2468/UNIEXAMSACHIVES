<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Per-submission chat thread. Lets offices discuss / clarify a form before
     * signing without polluting the form's signed sections.
     *
     * is_internal hides a comment from the requisitioner (e.g. Audit-to-Finance
     * notes) but keeps it visible to staff offices.
     */
    public function up(): void
    {
        Schema::create('form_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_submission_id')->constrained('form_submissions')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->text('message');
            $table->boolean('is_internal')->default(false);
            $table->timestamps();

            $table->index('form_submission_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('form_comments');
    }
};
