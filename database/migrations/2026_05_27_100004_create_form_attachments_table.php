<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Files uploaded against a form submission (invoices, receipts, supporting
     * docs). stage_slug ties the file to the stage that attached it so the
     * UI can show "uploaded by Finance" vs "uploaded by requisitioner" etc.
     *
     * Files live on the `public` disk under `form-attachments/{submission_id}/`
     * following the same pattern as memo email_attachments.
     */
    public function up(): void
    {
        Schema::create('form_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_submission_id')->constrained('form_submissions')->cascadeOnDelete();
            $table->string('stage_slug')->nullable();
            $table->string('name');
            $table->string('path');
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('size')->default(0);
            $table->foreignId('uploaded_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();

            $table->index(['form_submission_id', 'stage_slug']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('form_attachments');
    }
};
