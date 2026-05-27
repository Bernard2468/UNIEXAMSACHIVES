<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Single table that stores every form submission regardless of form type
     * (Payment Requisition, Purchase/Works Authorization, future forms).
     *
     * - form_slug binds the row to a code-defined FormDefinition class.
     * - section_data is a JSON object keyed by stage slug; each stage's fields
     *   live under its own key. This keeps the schema stable while letting new
     *   form types ship without DB migrations.
     * - workflow_history mirrors the UIMMS pattern: append-only audit log.
     */
    public function up(): void
    {
        Schema::create('form_submissions', function (Blueprint $table) {
            $table->id();
            $table->string('form_slug')->index();
            $table->string('form_code', 16);
            $table->string('reference')->unique();
            $table->string('title')->nullable();

            $table->enum('status', [
                'draft',
                'in_progress',
                'completed',
                'rejected',
                'cancelled',
                'archived',
            ])->default('draft');

            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();

            $table->string('current_stage')->nullable();
            $table->foreignId('current_assignee_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('current_office_id')->nullable()->constrained('offices')->nullOnDelete();

            $table->json('section_data')->nullable();
            $table->json('workflow_history')->nullable();

            $table->decimal('requisition_amount', 14, 2)->nullable();
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
            $table->boolean('referred_to_vc')->default(false);

            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->timestamp('archived_at')->nullable();

            $table->timestamps();

            $table->index(['form_slug', 'status']);
            $table->index(['status', 'current_assignee_id']);
            $table->index(['current_office_id', 'status']);
            $table->index('created_by');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('form_submissions');
    }
};
